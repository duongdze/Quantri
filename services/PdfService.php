<?php
/**
 * PdfService - Xuất hóa đơn / báo cáo ra file PDF bằng DomPDF
 */

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    /**
     * Xuất hóa đơn booking ra PDF và trả về cho trình duyệt
     *
     * @param array $booking  Thông tin booking (JOIN tour, departure, customer)
     * @param array $passengers Danh sách hành khách
     * @param bool  $download  true = download, false = hiển thị inline
     */
    public static function generateBookingInvoice(array $booking, array $passengers = [], bool $download = true)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        // Render template HTML
        ob_start();
        // Truyền biến vào template
        $data = $booking;
        $passengerList = $passengers;
        include PATH_ROOT . 'views/templates/invoice_pdf.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $bookingCode = 'BK' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT);
        $filename = 'hoa-don-' . $bookingCode . '.pdf';

        if ($download) {
            $dompdf->stream($filename, ['Attachment' => true]);
        } else {
            $dompdf->stream($filename, ['Attachment' => false]);
        }
        exit;
    }

    /**
     * Xuất báo cáo tổng hợp ra PDF
     */
    public static function generateReport(string $html, string $filename = 'bao-cao.pdf', string $orientation = 'portrait')
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
