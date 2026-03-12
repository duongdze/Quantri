<?php

/**
 * Export Service - Xử lý xuất báo cáo ra Excel và PDF
 */
class ExportService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = BaseModel::getPdo();
    }

    /**
     * Export báo cáo tài chính ra Excel
     */
    public function exportFinancialReport($data, $tourFinancials, $filename = null)
    {
        $filename = $filename ?? 'bao-cao-tai-chinh-' . date('Y-m-d') . '.xlsx';

        // Tạo file Excel sử dụng PHPExcel hoặc SimpleExcel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Tạo nội dung Excel format
        $excelContent = $this->createFinancialExcelContent($data, $tourFinancials);

        echo $excelContent;
        exit;
    }

    /**
     * Export báo cáo booking ra Excel
     */
    public function exportBookingReport($bookingStats, $bookings, $filename = null)
    {
        $filename = $filename ?? 'bao-cao-booking-' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $excelContent = $this->createBookingExcelContent($bookingStats, $bookings);

        echo $excelContent;
        exit;
    }

    /**
     * Export báo cáo feedback ra Excel
     */
    public function exportFeedbackReport($feedbackStats, $feedbacks, $filename = null)
    {
        $filename = $filename ?? 'bao-cao-phan-hoi-' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $excelContent = $this->createFeedbackExcelContent($feedbackStats, $feedbacks);

        echo $excelContent;
        exit;
    }

    /**
     * Export báo cáo ra HTML (Print-friendly)
     */
    public function exportToPDF($data, $type, $filename = null)
    {
        $filename = $filename ?? 'bao-cao-' . $type . '-' . date('Y-m-d') . '.html';

        try {
            // Tạo nội dung HTML cho báo cáo
            $htmlContent = $this->createPrintFriendlyHTML($data, $type);

            // Export as HTML file
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            echo $htmlContent;
            exit;
        } catch (Exception $e) {
            echo "Lỗi khi tạo báo cáo: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Tạo nội dung Excel cho báo cáo tài chính
     */
    private function createFinancialExcelContent($data, $tourFinancials)
    {
        $content = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Sheet 1: Tổng quan
        $content .= "Báo cáo Tài chính - " . date('d/m/Y') . "\n\n";
        $content .= "TỔNG QUAN\n";
        $content .= "Doanh thu\t" . number_format($data['total_revenue']) . " VNĐ\n";
        $content .= "Chi phí\t" . number_format($data['total_expense']) . " VNĐ\n";
        $content .= "Lợi nhuận\t" . number_format($data['profit']) . " VNĐ\n";
        $content .= "Tỷ suất lợi nhuận\t" . number_format($data['profit_margin'], 1) . "%\n";
        $content .= "Tăng trưởng\t" . number_format($data['revenue_growth'] ?? 0, 1) . "%\n\n";

        // Chi tiết theo tour
        $content .= "CHI TIẾT THEO TOUR\n";
        $content .= "Tour\tSố Booking\tDoanh thu\tChi phí\tLợi nhuận\tTỷ suất LN\tTrạng thái\n";

        foreach ($tourFinancials as $tour) {
            $status = $tour['profit'] >= 0 ? 'Lãi' : 'Lỗ';
            $content .= $tour['tour_name'] . "\t";
            $content .= $tour['booking_count'] . "\t";
            $content .= number_format($tour['revenue']) . "\t";
            $content .= number_format($tour['expense']) . "\t";
            $content .= number_format($tour['profit']) . "\t";
            $content .= number_format($tour['profit_margin'], 1) . "%\t";
            $content .= $status . "\n";
        }

        return $content;
    }

    /**
     * Tạo nội dung Excel cho báo cáo booking
     */
    private function createBookingExcelContent($bookingStats, $bookings)
    {
        $content = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Tổng quan
        $content .= "Báo cáo Booking - " . date('d/m/Y') . "\n\n";
        $content .= "TỔNG QUAN\n";
        $content .= "Tổng booking\t" . number_format($bookingStats['total_bookings']) . "\n";
        $content .= "Booking thành công\t" . number_format($bookingStats['successful_bookings']) . "\n";
        $content .= "Tỷ lệ thành công\t" . number_format($bookingStats['success_rate'], 1) . "%\n";
        $content .= "Tỷ lệ chuyển đổi\t" . number_format($bookingStats['conversion_rate'], 1) . "%\n";
        $content .= "Tổng khách hàng\t" . number_format($bookingStats['total_customers']) . "\n";
        $content .= "Tăng trưởng\t" . number_format($bookingStats['booking_growth'], 1) . "%\n\n";

        // Chi tiết bookings
        $content .= "CHI TIẾT BOOKINGS\n";
        $content .= "Mã BK\tKhách hàng\tTour\tNgày đi\tSố khách\tGiá trị\tTrạng thái\tNguồn\tNgày tạo\n";

        foreach ($bookings as $booking) {
            $totalCustomers = ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) + ($booking['infants'] ?? 0);
            $content .= "#" . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . "\t";
            $content .= ($booking['customer_name'] ?? 'N/A') . "\t";
            $content .= $booking['tour_name'] . "\t";
            $content .= date('d/m/Y', strtotime($booking['departure_date'])) . "\t";
            $content .= $totalCustomers . "\t";
            $content .= number_format($booking['final_price']) . "\t";
            $content .= $booking['status'] . "\t";
            $content .= ($booking['source'] ?? '') . "\t";
            $content .= date('d/m/Y', strtotime($booking['booking_date'])) . "\n";
        }

        return $content;
    }

    /**
     * Export báo cáo chuyển đổi ra Excel
     */
    public function exportConversionReport($conversionData, $topTours, $sourceConversion, $categoryConversion, $filename = null)
    {
        $filename = $filename ?? 'bao-cao-chuyen-doi-' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $excelContent = $this->createConversionExcelContent($conversionData, $topTours, $sourceConversion, $categoryConversion);

        echo $excelContent;
        exit;
    }

    /**
     * Export dashboard report ra Excel
     */
    public function exportDashboardReport($dashboardData, $filename = null)
    {
        $filename = $filename ?? 'dashboard-' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $excelContent = $this->createDashboardExcelContent($dashboardData);

        echo $excelContent;
        exit;
    }

    /**
     * Tạo nội dung Excel cho dashboard report
     */
    private function createDashboardExcelContent($dashboardData)
    {
        $content = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Header
        $content .= "DASHBOARD TỔNG QUAN - " . date('d/m/Y H:i:s') . "\n\n";

        // Financial KPIs
        $content .= "CHỈ SỐ TÀI CHÍNH\n";
        $financial = $dashboardData['financial'] ?? [];
        $content .= "Tổng doanh thu\t" . number_format($financial['total_revenue'] ?? 0) . " VNĐ\n";
        $content .= "Tổng chi phí\t" . number_format($financial['total_expense'] ?? 0) . " VNĐ\n";
        $content .= "Lợi nhuận\t" . number_format($financial['profit'] ?? 0) . " VNĐ\n";
        $content .= "Tỷ suất lợi nhuận\t" . number_format($financial['profit_margin'] ?? 0, 1) . "%\n";
        $content .= "Tăng trưởng doanh thu\t" . number_format($financial['revenue_growth'] ?? 0, 1) . "%\n\n";

        // Booking KPIs
        $content .= "CHỈ SỐ BOOKING\n";
        $bookings = $dashboardData['bookings'] ?? [];
        $content .= "Tổng bookings\t" . number_format($bookings['total_bookings'] ?? 0) . "\n";
        $content .= "Bookings thành công\t" . number_format($bookings['successful_bookings'] ?? 0) . "\n";
        $content .= "Tỷ lệ thành công\t" . number_format($bookings['success_rate'] ?? 0, 1) . "%\n";
        $content .= "Giá trị trung bình\t" . number_format($bookings['avg_booking_value'] ?? 0) . " VNĐ\n";
        $content .= "Tăng trưởng bookings\t" . number_format($bookings['booking_growth'] ?? 0, 1) . "%\n\n";

        // Conversion KPIs
        $content .= "CHỈ SỐ CHUYỂN ĐỔI\n";
        $conversion = $dashboardData['conversion'] ?? [];
        $content .= "Tổng inquiries\t" . number_format($conversion['total_inquiries'] ?? 0) . "\n";
        $content .= "Tỷ lệ chuyển đổi\t" . number_format($conversion['conversion_rates']['booking_to_payment'] ?? 0, 1) . "%\n";
        $content .= "Inquiry → Booking\t" . number_format($conversion['conversion_rates']['inquiry_to_booking'] ?? 0, 1) . "%\n";
        $content .= "Booking → Payment\t" . number_format($conversion['conversion_rates']['booking_to_payment'] ?? 0, 1) . "%\n";
        $content .= "Tăng trưởng chuyển đổi\t" . number_format($conversion['conversion_growth'] ?? 0, 1) . "%\n\n";

        // Feedback KPIs
        $content .= "CHỈ SỐ PHẢN HỒI\n";
        $feedback = $dashboardData['feedback'] ?? [];
        $content .= "Tổng phản hồi\t" . number_format($feedback['total_feedbacks'] ?? 0) . "\n";
        $content .= "Đánh giá trung bình\t" . number_format($feedback['avg_rating'] ?? 0, 1) . "/5.0\n";
        $content .= "Tỷ lệ phản hồi\t" . number_format($feedback['feedback_rate'] ?? 0, 1) . "%\n";
        $content .= "Tăng trưởng đánh giá\t" . number_format($feedback['rating_growth'] ?? 0, 1) . "%\n\n";

        // Tour Statistics
        $content .= "THỐNG KÊ TOURS\n";
        $tours = $dashboardData['tours'] ?? [];
        $content .= "Tổng tours\t" . number_format($tours['total_tours'] ?? 0) . "\n";
        $content .= "Tours hoạt động\t" . number_format($tours['active_tours'] ?? 0) . "\n";
        $content .= "Tổng danh mục\t" . number_format($tours['total_categories'] ?? 0) . "\n\n";

        // Top Revenue Tours
        if (isset($dashboardData['top_revenue_tours'])) {
            $content .= "TOP TOURS THEO DOANH THU\n";
            $content .= "Tour\tBookings\tDoanh thu\tLợi nhuận\tTỷ suất LN\n";

            foreach (array_slice($dashboardData['top_revenue_tours'], 0, 10) as $tour) {
                $content .= ($tour['tour_name'] ?? '') . "\t";
                $content .= ($tour['booking_count'] ?? 0) . "\t";
                $content .= number_format($tour['revenue'] ?? 0) . "\t";
                $content .= number_format($tour['profit'] ?? 0) . "\t";
                $content .= number_format($tour['profit_margin'] ?? 0, 1) . "%\n";
            }
            $content .= "\n";
        }

        // Top Rated Tours
        if (isset($dashboardData['top_rated_tours'])) {
            $content .= "TOP TOURS THEO ĐÁNH GIÁ\n";
            $content .= "Tour\tĐánh giá\tPhản hồi\tTỷ lệ chuyển đổi\n";

            foreach (array_slice($dashboardData['top_rated_tours'], 0, 10) as $tour) {
                $content .= ($tour['tour_name'] ?? '') . "\t";
                $content .= number_format($tour['avg_rating'] ?? 0, 1) . "\t";
                $content .= ($tour['feedback_count'] ?? 0) . "\t";
                $content .= number_format($tour['conversion_rate'] ?? 0, 1) . "%\n";
            }
            $content .= "\n";
        }

        // Revenue Distribution
        if (isset($dashboardData['revenue_distribution'])) {
            $content .= "PHÂN BỐ DOANH THU THEO DANH MỤC\n";
            $content .= "Danh mục\tDoanh thu\tTỷ trọng\n";

            $totalRevenue = array_sum($dashboardData['revenue_distribution']['data'] ?? []);
            foreach ($dashboardData['revenue_distribution']['labels'] as $index => $category) {
                $revenue = $dashboardData['revenue_distribution']['data'][$index] ?? 0;
                $percentage = $totalRevenue > 0 ? ($revenue / $totalRevenue) * 100 : 0;
                $content .= $category . "\t";
                $content .= number_format($revenue) . "\t";
                $content .= number_format($percentage, 1) . "%\n";
            }
            $content .= "\n";
        }

        // Conversion by Source
        if (isset($dashboardData['conversion_by_source'])) {
            $content .= "TỶ LỆ CHUYỂN ĐỔI THEO NGUỒN\n";
            $content .= "Nguồn\tBookings\tThành công\tTỷ lệ\tGiá trị TB\n";

            foreach ($dashboardData['conversion_by_source'] as $source) {
                $content .= ($source['source'] ?? 'Unknown') . "\t";
                $content .= ($source['total_bookings'] ?? 0) . "\t";
                $content .= ($source['successful_bookings'] ?? 0) . "\t";
                $content .= number_format($source['conversion_rate'] ?? 0, 1) . "%\t";
                $content .= number_format($source['avg_value'] ?? 0) . "\n";
            }
            $content .= "\n";
        }

        // Rating Distribution
        if (isset($dashboardData['rating_distribution'])) {
            $content .= "PHÂN PHỐI ĐÁNH GIÁ\n";
            $content .= "Số sao\tSố lượng\tTỷ lệ\n";

            $ratings = [5, 4, 3, 2, 1];
            $totalFeedbacks = array_sum($dashboardData['rating_distribution'] ?? []);

            foreach ($ratings as $index => $rating) {
                $count = $dashboardData['rating_distribution'][$index] ?? 0;
                $percentage = $totalFeedbacks > 0 ? ($count / $totalFeedbacks) * 100 : 0;
                $content .= $rating . " sao\t" . $count . "\t" . number_format($percentage, 1) . "%\n";
            }
            $content .= "\n";
        }

        // Recent Activities
        if (isset($dashboardData['recent_activities'])) {
            $content .= "HOẠT ĐỘNG GẦN ĐÂY\n";
            $content .= "Thời gian\tLoại\tMô tả\n";

            foreach ($dashboardData['recent_activities'] as $activity) {
                $content .= ($activity['time'] ?? '') . "\t";
                $content .= ($activity['title'] ?? '') . "\t";
                $content .= ($activity['description'] ?? '') . "\n";
            }
            $content .= "\n";
        }

        // Alerts
        if (isset($dashboardData['alerts'])) {
            $content .= "CẢNH BÁO\n";
            $content .= "Loại\tTiêu đề\tNội dung\tThời gian\n";

            foreach ($dashboardData['alerts'] as $alert) {
                $content .= ($alert['type'] ?? '') . "\t";
                $content .= ($alert['title'] ?? '') . "\t";
                $content .= ($alert['message'] ?? '') . "\t";
                $content .= ($alert['time'] ?? '') . "\n";
            }
        }

        return $content;
    }

    /**
     * Tạo nội dung Excel cho báo cáo chuyển đổi
     */
    private function createConversionExcelContent($conversionData, $topTours, $sourceConversion, $categoryConversion)
    {
        $content = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Tổng quan
        $content .= "Báo cáo Tỷ lệ Chuyển đổi - " . date('d/m/Y') . "\n\n";
        $content .= "TỔNG QUAN\n";
        $content .= "Tổng inquiries\t" . number_format($conversionData['total_inquiries']) . "\n";
        $content .= "Tổng bookings\t" . number_format($conversionData['total_bookings']) . "\n";
        $content .= "Inquiry → Booking\t" . number_format($conversionData['conversion_rates']['inquiry_to_booking'] ?? 0, 1) . "%\n";
        $content .= "Booking → Confirmation\t" . number_format($conversionData['conversion_rates']['booking_to_confirmation'] ?? 0, 1) . "%\n";
        $content .= "Booking → Payment\t" . number_format($conversionData['conversion_rates']['booking_to_payment'] ?? 0, 1) . "%\n";
        $content .= "Booking → Completion\t" . number_format($conversionData['conversion_rates']['booking_to_completion'] ?? 0, 1) . "%\n";
        $content .= "Tổng giá trị\t" . number_format($conversionData['total_value']) . " VNĐ\n";
        $content .= "Giá trị trung bình\t" . number_format($conversionData['avg_booking_value']) . " VNĐ\n\n";

        // Phân phối theo giai đoạn
        $content .= "PHÂN PHỐI THEO GIAI ĐOẠN\n";
        $content .= "Giai đoạn\tSố lượng\tTỷ lệ\n";
        $stages = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'deposited' => 'Đã đặt cọc',
            'paid' => 'Đã thanh toán',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];

        foreach ($stages as $key => $label) {
            $count = $conversionData['stage_counts'][$key] ?? 0;
            $rate = $conversionData['total_bookings'] > 0 ? ($count / $conversionData['total_bookings']) * 100 : 0;
            $content .= $label . "\t" . $count . "\t" . number_format($rate, 1) . "%\n";
        }
        $content .= "\n";

        // Top Tours theo tỷ lệ chuyển đổi
        $content .= "TOP TOURS THEO TỶ LỆ CHUYỂN ĐỔI\n";
        $content .= "Tour\tSố Booking\tThành công\tTỷ lệ\tGiá trị trung bình\n";

        foreach ($topTours as $tour) {
            $content .= $tour['tour_name'] . "\t";
            $content .= $tour['total_bookings'] . "\t";
            $content .= $tour['successful_bookings'] . "\t";
            $content .= number_format($tour['conversion_rate'], 1) . "%\t";
            $content .= number_format($tour['avg_value']) . " VNĐ\n";
        }
        $content .= "\n";

        // Tỷ lệ chuyển đổi theo nguồn
        $content .= "TỶ LỆ CHUYỂN ĐỔI THEO NGUỒN\n";
        $content .= "Nguồn\tSố Booking\tThành công\tTỷ lệ\tGiá trị trung bình\n";

        foreach ($sourceConversion as $source) {
            $content .= ($source['source'] ?? 'Unknown') . "\t";
            $content .= $source['total_bookings'] . "\t";
            $content .= $source['successful_bookings'] . "\t";
            $content .= number_format($source['conversion_rate'], 1) . "%\t";
            $content .= number_format($source['avg_value']) . " VNĐ\n";
        }
        $content .= "\n";

        // Tỷ lệ chuyển đổi theo danh mục
        $content .= "TỶ LỆ CHUYỂN ĐỔI THEO DANH MỤC\n";
        $content .= "Danh mục\tSố Booking\tThành công\tTỷ lệ\tGiá trị trung bình\n";

        foreach ($categoryConversion as $category) {
            $content .= $category['category_name'] . "\t";
            $content .= $category['total_bookings'] . "\t";
            $content .= $category['successful_bookings'] . "\t";
            $content .= number_format($category['conversion_rate'], 1) . "%\t";
            $content .= number_format($category['avg_value']) . " VNĐ\n";
        }

        return $content;
    }

    /**
     * Tạo nội dung PDF cho báo cáo chuyển đổi
     */
    private function createConversionPDFContent($data)
    {
        $html = '<div class="section">
            <h2>Tổng quan Tỷ lệ Chuyển đổi</h2>
            <table>
                <tr><td>Tổng Inquiries</td><td class="text-right">' . number_format($data['total_inquiries']) . '</td></tr>
                <tr><td>Tổng Bookings</td><td class="text-right">' . number_format($data['total_bookings']) . '</td></tr>
                <tr><td>Tỷ lệ Chuyển đổi</td><td class="text-right">' . number_format($data['conversion_rates']['booking_to_payment'] ?? 0, 1) . '%</td></tr>
                <tr><td>Tổng Giá trị</td><td class="text-right">' . number_format($data['total_value']) . ' VNĐ</td></tr>
            </table>
        </div>';

        if (isset($data['topTours'])) {
            $html .= '<div class="section">
                <h2>Top Tours theo Tỷ lệ Chuyển đổi</h2>
                <table>
                    <tr>
                        <th>Tour</th>
                        <th class="text-right">Số Booking</th>
                        <th class="text-right">Thành công</th>
                        <th class="text-right">Tỷ lệ</th>
                    </tr>';

            foreach ($data['topTours'] as $tour) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($tour['tour_name']) . '</td>
                    <td class="text-right">' . $tour['total_bookings'] . '</td>
                    <td class="text-right">' . $tour['successful_bookings'] . '</td>
                    <td class="text-right">' . number_format($tour['conversion_rate'], 1) . '%</td>
                </tr>';
            }

            $html .= '</table></div>';
        }

        return $html;
    }

    /**
     * Tạo nội dung Excel cho báo cáo feedback
     */
    private function createFeedbackExcelContent($feedbackStats, $feedbacks)
    {
        $content = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Tổng quan
        $content .= "Báo cáo Phản hồi - " . date('d/m/Y') . "\n\n";
        $content .= "TỔNG QUAN\n";
        $content .= "Đánh giá trung bình\t" . number_format($feedbackStats['avg_rating'], 1) . "/5.0\n";
        $content .= "Tổng phản hồi\t" . number_format($feedbackStats['total_feedbacks']) . "\n";
        $content .= "Phản hồi tích cực\t" . number_format($feedbackStats['positive_feedbacks']) . "\n";
        $content .= "Phản hồi tiêu cực\t" . number_format($feedbackStats['negative_feedbacks']) . "\n";
        $content .= "Tỷ lệ phản hồi\t" . number_format($feedbackStats['feedback_rate'], 1) . "%\n";
        $content .= "Tăng trưởng\t" . number_format($feedbackStats['rating_growth'], 1) . "%\n\n";

        // Chi tiết feedbacks
        $content .= "CHI TIẾT PHẢN HỒI\n";
        $content .= "Mã PH\tLoại\tTour/Đối tượng\tKhách hàng\tĐánh giá\tNội dung\tNgày\tTình cảm\n";

        foreach ($feedbacks as $feedback) {
            $content .= "#" . str_pad($feedback['id'], 6, '0', STR_PAD_LEFT) . "\t";
            $content .= ($feedback['feedback_type'] ?? 'tour') . "\t";
            $content .= ($feedback['target_name'] ?? 'N/A') . "\t";
            $content .= ($feedback['customer_name'] ?? 'N/A') . "\t";
            $content .= ($feedback['rating'] ?? 0) . "/5\t";
            $content .= substr($feedback['comment'] ?? '', 0, 50) . "\t";
            $content .= date('d/m/Y', strtotime($feedback['created_at'])) . "\t";
            $content .= ($feedback['sentiment'] ?? 'neutral') . "\n";
        }

        return $content;
    }

    /**
     * Tạo nội dung HTML print-friendly cho báo cáo
     */
    private function createPrintFriendlyHTML($data, $type)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Báo cáo ' . ucfirst($type) . ' - ' . date('d/m/Y') . '</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 1cm; 
        }
        
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header { 
            text-align: center; 
            border-bottom: 3px solid #333; 
            padding-bottom: 20px; 
            margin-bottom: 30px;
        }
        
        .header h1 { 
            margin: 0; 
            color: #333; 
            font-size: 24px;
        }
        
        .header p { 
            margin: 5px 0 0 0; 
            color: #666; 
            font-size: 14px;
        }
        
        .section { 
            margin: 30px 0; 
            page-break-inside: avoid;
        }
        
        .section h2 { 
            color: #333; 
            border-bottom: 2px solid #ccc; 
            padding-bottom: 8px; 
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .summary-label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
            font-size: 11px;
        }
        
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            vertical-align: top;
        }
        
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-transform: uppercase;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .positive { color: #28a745; font-weight: bold; }
        .negative { color: #dc3545; font-weight: bold; }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        @media print {
            body { margin: 0; padding: 15px; }
            .section { page-break-inside: avoid; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>';

        // Header
        $html .= '<div class="header">
            <h1>Báo cáo ' . ucfirst($type) . '</h1>
            <p>Ngày xuất: ' . date('d/m/Y H:i:s') . '</p>
            <p>Đơn vị: Tour Management System</p>
        </div>';

        // Nội dung theo loại báo cáo
        switch ($type) {
            case 'financial':
                $html .= $this->createFinancialHTMLContent($data);
                break;
            case 'booking':
                $html .= $this->createBookingHTMLContent($data);
                break;
            case 'feedback':
                $html .= $this->createFeedbackHTMLContent($data);
                break;
            case 'conversion':
                $html .= $this->createConversionHTMLContent($data);
                break;
            case 'dashboard':
                $html .= $this->createDashboardHTMLContent($data);
                break;
            default:
                $html .= '<div class="section"><p>Không có dữ liệu cho loại báo cáo này.</p></div>';
        }

        // Footer
        $html .= '<div class="footer">
            <p>Báo cáo được tạo tự động bởi Tour Management System</p>
            <p>© ' . date('Y') . ' - All rights reserved</p>
        </div>';

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Tạo nội dung HTML cho báo cáo tài chính
     */
    private function createFinancialHTMLContent($data)
    {
        $html = '<div class="section">
            <h2>Tổng quan Tài chính</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Doanh thu</div>
                    <div class="summary-value">' . number_format($data['total_revenue']) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Chi phí</div>
                    <div class="summary-value">' . number_format($data['total_expense']) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Lợi nhuận</div>
                    <div class="summary-value ' . ($data['profit'] >= 0 ? 'positive' : 'negative') . '">' . number_format($data['profit']) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ suất lợi nhuận</div>
                    <div class="summary-value">' . number_format($data['profit_margin'], 1) . '%</div>
                </div>
            </div>
        </div>';

        if (isset($data['tourFinancials'])) {
            $html .= '<div class="section">
                <h2>Chi tiết theo Tour</h2>
                <table>
                    <tr>
                        <th>Tour</th>
                        <th class="text-right">Số Booking</th>
                        <th class="text-right">Doanh thu</th>
                        <th class="text-right">Chi phí</th>
                        <th class="text-right">Lợi nhuận</th>
                        <th class="text-right">Tỷ suất LN</th>
                        <th class="text-right">Trạng thái</th>
                    </tr>';

            foreach ($data['tourFinancials'] as $tour) {
                $profitClass = $tour['profit'] >= 0 ? 'positive' : 'negative';
                $status = $tour['profit'] >= 0 ? 'Lãi' : 'Lỗ';
                $html .= '<tr>
                    <td>' . htmlspecialchars($tour['tour_name']) . '</td>
                    <td class="text-right">' . $tour['booking_count'] . '</td>
                    <td class="text-right">' . number_format($tour['revenue']) . '</td>
                    <td class="text-right">' . number_format($tour['expense']) . '</td>
                    <td class="text-right ' . $profitClass . '">' . number_format($tour['profit']) . '</td>
                    <td class="text-right">' . number_format($tour['profit_margin'], 1) . '%</td>
                    <td class="text-center ' . $profitClass . '">' . $status . '</td>
                </tr>';
            }

            $html .= '</table></div>';
        }

        return $html;
    }

    /**
     * Tạo nội dung HTML cho báo cáo booking
     */
    private function createBookingHTMLContent($data)
    {
        $html = '<div class="section">
            <h2>Tổng quan Booking</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Tổng booking</div>
                    <div class="summary-value">' . number_format($data['total_bookings']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Booking thành công</div>
                    <div class="summary-value">' . number_format($data['successful_bookings']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ lệ thành công</div>
                    <div class="summary-value">' . number_format($data['success_rate'], 1) . '%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ lệ chuyển đổi</div>
                    <div class="summary-value">' . number_format($data['conversion_rate'], 1) . '%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tổng khách hàng</div>
                    <div class="summary-value">' . number_format($data['total_customers']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Giá trị TB</div>
                    <div class="summary-value">' . number_format($data['avg_booking_value'] ?? 0) . ' VNĐ</div>
                </div>
            </div>
        </div>';

        if (isset($data['bookings'])) {
            $html .= '<div class="section">
                <h2>Chi tiết Bookings (Top 50)</h2>
                <table>
                    <tr>
                        <th>Mã Booking</th>
                        <th>Khách hàng</th>
                        <th>Tour</th>
                        <th class="text-right">Số khách</th>
                        <th class="text-right">Giá trị</th>
                        <th>Trạng thái</th>
                        <th>Ngày đi</th>
                        <th>Nguồn</th>
                        <th>Ngày tạo</th>
                    </tr>';

            foreach (array_slice($data['bookings'], 0, 50) as $booking) {
                $totalCustomers = ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) + ($booking['infants'] ?? 0);
                $html .= '<tr>
                    <td>#' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . '</td>
                    <td>' . htmlspecialchars($booking['customer_name'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($booking['tour_name']) . '</td>
                    <td class="text-right">' . $totalCustomers . '</td>
                    <td class="text-right">' . number_format($booking['final_price']) . ' VNĐ</td>
                    <td class="text-center">' . $booking['status'] . '</td>
                    <td>' . date('d/m/Y', strtotime($booking['departure_date'])) . '</td>
                    <td>' . ($booking['source'] ?? '') . '</td>
                    <td>' . date('d/m/Y', strtotime($booking['booking_date'])) . '</td>
                </tr>';
            }

            $html .= '</table></div>';
        }

        return $html;
    }

    /**
     * Tạo nội dung HTML cho báo cáo feedback
     */
    private function createFeedbackHTMLContent($data)
    {
        $html = '<div class="section">
            <h2>Tổng quan Phản hồi</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Đánh giá trung bình</div>
                    <div class="summary-value">' . number_format($data['avg_rating'], 1) . '/5.0</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tổng phản hồi</div>
                    <div class="summary-value">' . number_format($data['total_feedbacks']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Phản hồi tích cực</div>
                    <div class="summary-value positive">' . number_format($data['positive_feedbacks']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Phản hồi tiêu cực</div>
                    <div class="summary-value negative">' . number_format($data['negative_feedbacks']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ lệ phản hồi</div>
                    <div class="summary-value">' . number_format($data['feedback_rate'], 1) . '%</div>
                </div>
            </div>
        </div>';

        if (isset($data['feedbacks'])) {
            $html .= '<div class="section">
                <h2>Chi tiết Phản hồi (Top 50)</h2>
                <table>
                    <tr>
                        <th>Mã PH</th>
                        <th>Loại</th>
                        <th>Khách hàng</th>
                        <th>Tour/Đối tượng</th>
                        <th class="text-center">Đánh giá</th>
                        <th>Nội dung</th>
                        <th>Tình cảm</th>
                        <th>Ngày</th>
                    </tr>';

            foreach (array_slice($data['feedbacks'], 0, 50) as $feedback) {
                $sentimentClass = $feedback['sentiment'] === 'positive' ? 'positive' : ($feedback['sentiment'] === 'negative' ? 'negative' : '');
                $sentimentIcon = $feedback['sentiment'] === 'positive' ? '😊' : ($feedback['sentiment'] === 'negative' ? '😞' : '😐');

                $html .= '<tr>
                    <td>#' . str_pad($feedback['id'], 6, '0', STR_PAD_LEFT) . '</td>
                    <td>' . ($feedback['feedback_type'] ?? 'tour') . '</td>
                    <td>' . htmlspecialchars($feedback['customer_name'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($feedback['target_name'] ?? 'N/A') . '</td>
                    <td class="text-center">' . ($feedback['rating'] ?? 0) . '/5</td>
                    <td>' . htmlspecialchars(substr($feedback['comment'] ?? '', 0, 100)) . '</td>
                    <td class="text-center ' . $sentimentClass . '">' . $sentimentIcon . ' ' . ($feedback['sentiment'] ?? 'neutral') . '</td>
                    <td>' . date('d/m/Y', strtotime($feedback['created_at'])) . '</td>
                </tr>';
            }

            $html .= '</table></div>';
        }

        return $html;
    }

    /**
     * Tạo nội dung HTML cho báo cáo chuyển đổi
     */
    private function createConversionHTMLContent($data)
    {
        $html = '<div class="section">
            <h2>Tổng quan Tỷ lệ Chuyển đổi</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Tổng Inquiries</div>
                    <div class="summary-value">' . number_format($data['total_inquiries']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tổng Bookings</div>
                    <div class="summary-value">' . number_format($data['total_bookings']) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ lệ Inquiry → Booking</div>
                    <div class="summary-value">' . number_format($data['conversion_rates']['inquiry_to_booking'] ?? 0, 1) . '%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ lệ Booking → Payment</div>
                    <div class="summary-value">' . number_format($data['conversion_rates']['booking_to_payment'] ?? 0, 1) . '%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tổng giá trị</div>
                    <div class="summary-value">' . number_format($data['total_value']) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Giá trị TB</div>
                    <div class="summary-value">' . number_format($data['avg_booking_value']) . ' VNĐ</div>
                </div>
            </div>
        </div>';

        if (isset($data['topTours'])) {
            $html .= '<div class="section">
                <h2>Top Tours theo Tỷ lệ Chuyển đổi</h2>
                <table>
                    <tr>
                        <th>Tour</th>
                        <th class="text-right">Số Booking</th>
                        <th class="text-right">Thành công</th>
                        <th class="text-right">Tỷ lệ</th>
                        <th class="text-right">Giá trị TB</th>
                    </tr>';

            foreach ($data['topTours'] as $tour) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($tour['tour_name']) . '</td>
                    <td class="text-right">' . $tour['total_bookings'] . '</td>
                    <td class="text-right">' . $tour['successful_bookings'] . '</td>
                    <td class="text-right">' . number_format($tour['conversion_rate'], 1) . '%</td>
                    <td class="text-right">' . number_format($tour['avg_value']) . ' VNĐ</td>
                </tr>';
            }

            $html .= '</table></div>';
        }

        return $html;
    }

    /**
     * Tạo nội dung HTML cho báo cáo dashboard
     */
    private function createDashboardHTMLContent($data)
    {
        $html = '<div class="section">
            <h2>Tổng quan Dashboard</h2>';

        // Financial KPIs
        if (isset($data['financial'])) {
            $financial = $data['financial'];
            $html .= '<h3>Chỉ số Tài chính</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Tổng doanh thu</div>
                    <div class="summary-value">' . number_format($financial['total_revenue'] ?? 0) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tổng chi phí</div>
                    <div class="summary-value">' . number_format($financial['total_expense'] ?? 0) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Lợi nhuận</div>
                    <div class="summary-value ' . (($financial['profit'] ?? 0) >= 0 ? 'positive' : 'negative') . '">' . number_format($financial['profit'] ?? 0) . ' VNĐ</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ suất lợi nhuận</div>
                    <div class="summary-value">' . number_format($financial['profit_margin'] ?? 0, 1) . '%</div>
                </div>
            </div>';
        }

        // Booking KPIs
        if (isset($data['bookings'])) {
            $bookings = $data['bookings'];
            $html .= '<h3>Chỉ số Booking</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Tổng bookings</div>
                    <div class="summary-value">' . number_format($bookings['total_bookings'] ?? 0) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Bookings thành công</div>
                    <div class="summary-value">' . number_format($bookings['successful_bookings'] ?? 0) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Tỷ lệ thành công</div>
                    <div class="summary-value">' . number_format($bookings['success_rate'] ?? 0, 1) . '%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Giá trị TB</div>
                    <div class="summary-value">' . number_format($bookings['avg_booking_value'] ?? 0) . ' VNĐ</div>
                </div>
            </div>';
        }

        $html .= '</div>';
        return $html;
    }
}
