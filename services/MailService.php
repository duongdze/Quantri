<?php
/**
 * MailService – Gói wrapper PHPMailer
 * 
 * Cách dùng:
 *   MailService::send('to@email.com', 'Tên người nhận', 'Tiêu đề', '<p>Nội dung HTML</p>');
 * 
 * Cấu hình SMTP trong configs/env.php:
 *   MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM, MAIL_FROM_NAME, MAIL_ENABLED
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

// Load autoloader nếu chưa load
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $autoload = PATH_ROOT . 'vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}

class MailService
{
    /**
     * Gửi email HTML
     *
     * @param string|array $to       Email người nhận (string hoặc ['email'=>'...','name'=>'...'])
     * @param string       $subject  Tiêu đề email
     * @param string       $body     Nội dung HTML
     * @param string       $altBody  Nội dung plain-text fallback
     * @return bool
     */
    public static function send($to, string $subject, string $body, string $altBody = ''): bool
    {
        // Nếu MAIL_ENABLED = false => chỉ log, không gửi
        if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
            error_log("[MailService] MAIL_ENABLED=false. Would send to: " . (is_array($to) ? $to['email'] : $to) . " | Subject: $subject");
            return true; // Giả lập thành công khi đang dev
        }

        try {
            $mail = new PHPMailer(true);

            // SMTP settings
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPDebug  = SMTP::DEBUG_OFF; // Bật DEBUG_SERVER để debug

            // Sender
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);

            // Recipient
            if (is_array($to)) {
                $mail->addAddress($to['email'], $to['name'] ?? '');
            } else {
                $mail->addAddress($to);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::wrapLayout($subject, $body);
            $mail->AltBody = $altBody ?: strip_tags($body);

            $mail->send();
            return true;
        } catch (MailerException $e) {
            error_log("[MailService] Gửi mail thất bại: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gói nội dung vào template HTML đẹp
     */
    private static function wrapLayout(string $title, string $body): string
    {
        $siteUrl  = defined('BASE_URL') ? BASE_URL : '#';
        $siteName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'VietTour';
        $year     = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$title}</title>
  <style>
    body { margin:0; padding:0; background:#f4f6fb; font-family:'Segoe UI',Arial,sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); }
    .header { background:linear-gradient(135deg,#0a1628,#1e6fff); padding:32px 40px; text-align:center; }
    .header h1 { color:#fff; font-size:22px; margin:0; font-weight:800; }
    .header p  { color:rgba(255,255,255,.75); font-size:13px; margin:6px 0 0; }
    .body   { padding:36px 40px; line-height:1.8; }
    .body h2 { color:#1e6fff; font-size:18px; margin-top:0; }
    .btn-primary { display:inline-block; padding:13px 28px; background:#1e6fff; color:#fff !important;
                   text-decoration:none; border-radius:50px; font-weight:700; font-size:15px; margin:20px 0; }
    .info-box { background:#f0f7ff; border-left:4px solid #1e6fff; border-radius:0 8px 8px 0;
                padding:16px 20px; margin:20px 0; }
    .info-box p { margin:4px 0; font-size:14px; }
    .info-box strong { color:#111; }
    .divider { border:none; border-top:1px solid #e5e7eb; margin:24px 0; }
    .footer { background:#f8fafc; text-align:center; padding:20px 40px; font-size:12px; color:#9ca3af; }
    .footer a { color:#1e6fff; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>✈️ {$siteName}</h1>
      <p>Nền tảng du lịch trực tuyến hàng đầu Việt Nam</p>
    </div>
    <div class="body">
      {$body}
    </div>
    <div class="footer">
      &copy; {$year} <a href="{$siteUrl}">{$siteName}</a>. Mọi quyền được bảo lưu.<br>
      Bạn nhận được email này vì đã đăng ký tài khoản tại website của chúng tôi.
    </div>
  </div>
</body>
</html>
HTML;
    }

    // ============================================================
    // Các template email phổ biến
    // ============================================================

    /**
     * Gửi email xác nhận đặt tour
     */
    public static function sendBookingConfirmation(array $user, array $booking, array $tour): bool
    {
        $name        = htmlspecialchars($user['full_name'] ?? 'Quý khách');
        $tourName    = htmlspecialchars($tour['name'] ?? '');
        $bookingCode = htmlspecialchars($booking['booking_code'] ?? '#' . $booking['id']);
        $totalPrice  = number_format($booking['total_price'] ?? 0, 0, ',', '.') . 'đ';
        $status      = 'Chờ xác nhận';
        $siteUrl     = defined('BASE_URL') ? BASE_URL : '#';

        $body = <<<HTML
<h2>Cảm ơn bạn đã đặt tour!</h2>
<p>Xin chào <strong>{$name}</strong>,</p>
<p>Đơn đặt tour của bạn đã được tiếp nhận thành công. Chúng tôi sẽ liên hệ xác nhận trong vòng 24 giờ làm việc.</p>

<div class="info-box">
  <p><strong>Mã đơn:</strong> {$bookingCode}</p>
  <p><strong>Tour:</strong> {$tourName}</p>
  <p><strong>Tổng tiền:</strong> {$totalPrice}</p>
  <p><strong>Trạng thái:</strong> {$status}</p>
</div>

<a href="{$siteUrl}?action=my-bookings" class="btn-primary">Xem đơn hàng của tôi</a>

<hr class="divider">
<p style="color:#6b7280;font-size:13px">Nếu bạn có thắc mắc, hãy liên hệ với chúng tôi qua email hoặc hotline trên website.</p>
HTML;

        return self::send(
            ['email' => $user['email'], 'name' => $user['full_name']],
            "Xác nhận đặt tour: {$tourName} [{$bookingCode}]",
            $body
        );
    }

    /**
     * Gửi email link đặt lại mật khẩu
     */
    public static function sendPasswordReset(array $user, string $resetLink): bool
    {
        $name = htmlspecialchars($user['full_name'] ?? 'Quý khách');

        $body = <<<HTML
<h2>Đặt lại mật khẩu</h2>
<p>Xin chào <strong>{$name}</strong>,</p>
<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Nhấn vào nút bên dưới để tạo mật khẩu mới:</p>

<a href="{$resetLink}" class="btn-primary">Đặt lại mật khẩu</a>

<hr class="divider">
<p style="color:#6b7280;font-size:13px">
  Link này có hiệu lực trong <strong>1 giờ</strong>. Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.
</p>
<p style="color:#6b7280;font-size:12px">Hoặc dán link sau vào trình duyệt: <br><a href="{$resetLink}">{$resetLink}</a></p>
HTML;

        return self::send(
            ['email' => $user['email'], 'name' => $user['full_name']],
            'Đặt lại mật khẩu – VietTour',
            $body
        );
    }

    /**
     * Thông báo admin khi có booking mới
     */
    public static function notifyAdminNewBooking(string $adminEmail, array $booking, array $tour, array $user): bool
    {
        $bookingCode = $booking['booking_code'] ?? '#' . $booking['id'];
        $tourName    = htmlspecialchars($tour['name'] ?? '');
        $userName    = htmlspecialchars($user['full_name'] ?? '');
        $totalPrice  = number_format($booking['total_price'] ?? 0, 0, ',', '.') . 'đ';
        $adminUrl    = defined('BASE_URL_ADMIN') ? BASE_URL_ADMIN : '#';

        $body = <<<HTML
<h2>🔔 Đơn đặt tour mới!</h2>
<p>Có một đơn đặt tour mới vừa được tạo:</p>

<div class="info-box">
  <p><strong>Mã đơn:</strong> {$bookingCode}</p>
  <p><strong>Tour:</strong> {$tourName}</p>
  <p><strong>Khách hàng:</strong> {$userName}</p>
  <p><strong>Tổng tiền:</strong> {$totalPrice}</p>
</div>

<a href="{$adminUrl}&action=bookings" class="btn-primary">Quản lý đặt tour</a>
HTML;

        return self::send($adminEmail, "Đơn đặt tour mới [{$bookingCode}]", $body);
    }
}
