<?php

// Assumes Composer's autoloader is included where this class is used.
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class QRGenerator {

    /**
     * Generates a QR code image and saves it to a file.
     *
     * @param string $data The data to encode in the QR code (e.g., a unique ticket code).
     * @param string $filename The desired filename (without extension).
     * @return string The file path to the generated QR code image.
     * @throws Exception If the QR code directory cannot be created.
     */
    public static function generate($data, $filename) {
        $qr_code_dir = __DIR__ . '/../upload/qrcodes/';

        // Ensure the directory exists
        if (!is_dir($qr_code_dir)) {
            // This check is redundant if the previous step (`mkdir`) was successful, but it's good practice.
            if (!mkdir($qr_code_dir, 0755, true)) {
                throw new Exception("Could not create QR code directory: " . $qr_code_dir);
            }
        }

        $filepath = $qr_code_dir . $filename . '.png';

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        // Save the result to a file
        $result->saveToFile($filepath);

        // Return the relative path for use in web pages
        return 'upload/qrcodes/' . $filename . '.png';
    }
}
