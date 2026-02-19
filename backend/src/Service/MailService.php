<?php

namespace App\Service;

use Mailjet\Client;
use \Mailjet\Resources;

class MailService
{
  private Client $mj;

  public function __construct()
  {
    $this->mj = new Client($_ENV['MAILJET_API_KEY'], $_ENV['MAILJET_API_SECRET'], true, ['version' => 'v3.1']);
  }

  public function sendEmail(string $to, string $subject, string $content, ?string $from = null): void
  {
    $SENDER_EMAIL = $from ?? 'contact@watchcorn.alvincrn.fr';
    $body = [
      'Messages' => [
        [
          'From' => [
            'Email' => "$SENDER_EMAIL"
          ],
          'To' => [
            [
              'Email' => "$to"
            ]
          ],
          'Subject' => "$subject",
          'HTMLPart' => "$content"
        ]
      ]
    ];

    $this->mj->post(Resources::$Email, ['body' => $body]);
  }
}