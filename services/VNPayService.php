<?php
/**
 * VNPayService - Tích hợp cổng thanh toán VNPay (Sandbox)
 * Docs: https://sandbox.vnpayment.vn/apis/
 */

class VNPayService
{
    /**
     * Tạo URL thanh toán VNPay
     *
     * @param array $booking  Thông tin booking (id, total_price, ...)
     * @param string $orderInfo  Mô tả đơn hàng
     * @param string $returnUrl  URL callback sau khi thanh toán
     * @return string URL redirect sang VNPay
     */
    public static function createPaymentUrl(array $booking, string $orderInfo, string $returnUrl): string
    {
        $vnp_TmnCode    = VNPAY_TMN_CODE;
        $vnp_HashSecret = VNPAY_HASH_SECRET;
        $vnp_Url        = VNPAY_URL;

        $bookingCode = 'BK' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT);

        $vnp_TxnRef   = $bookingCode . '_' . time(); // Mã giao dịch duy nhất
        $vnp_Amount   = (int)$booking['total_price'] * 100; // VNPay tính bằng đồng × 100
        $vnp_Locale   = 'vn';
        $vnp_IpAddr   = '127.0.0.1'; // Bắt buộc IPv4 format cho VNPay, ::1 của localhost sẽ gây lỗi chữ ký

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => $vnp_Amount,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $vnp_IpAddr,
            "vnp_Locale"     => $vnp_Locale,
            "vnp_OrderInfo"  => $orderInfo,
            "vnp_OrderType"  => "billpayment",
            "vnp_ReturnUrl"  => $returnUrl,
            "vnp_TxnRef"     => $vnp_TxnRef,
        ];

        ksort($inputData);

        $query    = "";
        $hashdata = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $query .= 'vnp_SecureHash=' . $vnpSecureHash;

        return $vnp_Url . "?" . $query;
    }

    /**
     * Xác thực response từ VNPay (Return URL)
     *
     * @param array $vnpData Dữ liệu $_GET từ VNPay callback
     * @return array ['success' => bool, 'booking_code' => string, 'message' => string]
     */
    public static function verifyReturn(array $vnpData): array
    {
        $vnp_HashSecret = VNPAY_HASH_SECRET;
        $vnp_SecureHash = $vnpData['vnp_SecureHash'] ?? '';

        // Loại bỏ các tham số hash để tính lại
        $inputData = [];
        foreach ($vnpData as $key => $value) {
            if (substr($key, 0, 4) == "vnp_" && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
                $inputData[$key] = $value;
            }
        }
        ksort($inputData);

        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Parse booking code từ txn ref (BK000001_timestamp)
        $txnRef = $vnpData['vnp_TxnRef'] ?? '';
        $bookingCode = explode('_', $txnRef)[0] ?? '';

        if ($secureHash !== $vnp_SecureHash) {
            return [
                'success'      => false,
                'booking_code' => $bookingCode,
                'message'      => 'Chữ ký không hợp lệ! Giao dịch có thể bị giả mạo.',
                'response_code' => '97'
            ];
        }

        $responseCode = $vnpData['vnp_ResponseCode'] ?? '99';

        if ($responseCode == '00') {
            return [
                'success'        => true,
                'booking_code'   => $bookingCode,
                'message'        => 'Thanh toán thành công!',
                'response_code'  => '00',
                'amount'         => ($vnpData['vnp_Amount'] ?? 0) / 100,
                'transaction_no' => $vnpData['vnp_TransactionNo'] ?? '',
                'bank_code'      => $vnpData['vnp_BankCode'] ?? '',
                'pay_date'       => $vnpData['vnp_PayDate'] ?? '',
            ];
        }

        $messages = [
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần.',
            '11' => 'Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Khách hàng hủy giao dịch.',
            '51' => 'Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'KH nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Lỗi không xác định.',
        ];

        return [
            'success'        => false,
            'booking_code'   => $bookingCode,
            'message'        => $messages[$responseCode] ?? 'Giao dịch thất bại (mã lỗi: ' . $responseCode . ')',
            'response_code'  => $responseCode,
        ];
    }
}
