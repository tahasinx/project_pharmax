<?php

namespace App\Helpers;

use App\Services\NotificationService;

class NotificationHelper
{
    /**
     * Send email notification
     *
     * @param  string  $to  Email address
     * @param  string  $subject  Email subject
     * @param  string  $message  Email message
     * @param  array  $options  Additional options
     * @return array Result array with success status and message
     */
    public static function sendEmail(string $to, string $subject, string $message, array $options = []): array
    {
        $service = new NotificationService;

        return $service->sendEmail($to, $subject, $message, $options);
    }

    /**
     * Send SMS notification
     *
     * @param  string  $phone  Phone number
     * @param  string  $message  SMS message
     * @param  array  $options  Additional options
     * @return array Result array with success status and message
     */
    public static function sendSms(string $phone, string $message, array $options = []): array
    {
        $service = new NotificationService;

        return $service->sendSms($phone, $message, $options);
    }

    /**
     * Send email with template (future enhancement)
     *
     * @param  string  $to  Email address
     * @param  string  $template  Template name
     * @param  array  $data  Template data
     * @param  array  $options  Additional options
     * @return array Result array with success status and message
     */
    public static function sendEmailTemplate(string $to, string $template, array $data = [], array $options = []): array
    {
        // For now, just send raw email
        // Future: implement template system
        $subject = $data['subject'] ?? 'Notification';
        $message = $data['message'] ?? 'You have a new notification';

        return self::sendEmail($to, $subject, $message, $options);
    }

    /**
     * Send SMS with template (future enhancement)
     *
     * @param  string  $phone  Phone number
     * @param  string  $template  Template name
     * @param  array  $data  Template data
     * @param  array  $options  Additional options
     * @return array Result array with success status and message
     */
    public static function sendSmsTemplate(string $phone, string $template, array $data = [], array $options = []): array
    {
        // For now, just send raw SMS
        // Future: implement template system
        $message = $data['message'] ?? 'You have a new notification';

        return self::sendSms($phone, $message, $options);
    }
}
