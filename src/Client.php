<?php

namespace IgorVolgin\Mailtrap;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use IgorVolgin\Mailtrap\Exceptions\MailtrapException;

class Client
{
    /** @var string */
    protected string $apiToken;

    /** @var GuzzleHttp\Client */
    public HttpClient|GuzzleHttp\Client $http;

    /** @var array */
    protected array $errors = [];

    /**
     * Get new Client instance.
     *
     * @param  string  $apiToken
     */
    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
        $this->http = new HttpClient([
            'base_uri' => 'https://send.api.mailtrap.io/api/',
        ]);
    }

    /**
     * Make a new API request.
     *
     * @param  string  $uri  request uri relative to base uri set in constructor
     * @param  array  $body  body
     *
     * @return string
     */
    public function request(string $uri, array $body): ?string
    {
        $this->errors = [];

        $headers = [
            'Api-Token' => $this->apiToken,
            'content-type' => 'application/json',
        ];

        try {
            $response = $this->http->post($uri, [
                'headers' => $headers,
                'json' => $body,
            ]);
        } catch (RequestException $guzzleException) {
            $mailtrapException = MailtrapException::create($guzzleException);
            $this->setErrors($mailtrapException);

            return null;
        }
        $resBody = $response->getBody()->getContents();
        $json = json_decode($resBody);

        return (json_last_error() === JSON_ERROR_NONE) ? $json : (string) $resBody;
    }

    /**
     * Make a send email API request.
     *
     * @param  string  $fromEmail
     * @param  string  $fromName
     * @param  string  $toEmail
     * @param  string  $toName
     * @param  string  $subject
     * @param  string  $text
     * @param  string  $html
     *
     * @return string|null
     */
    public function send(
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $toName,
        string $subject,
        string $text,
        string $html
    ): ?string {
        $body = [
            "to" => [
                [
                    "email" => $toEmail,
                    "name" => $toName,
                ],
            ],
            "from" => [
                "email" => $fromEmail,
                "name" => $fromName,
            ],
            "subject" => $subject,
            "text" => $text,
            "html" => $html,
        ];

        return $this->request('send', $body);
    }

    /**
     * Set response errors.
     *
     * @param  MailtrapException  $exception
     */
    protected function setErrors(MailtrapException $exception): void
    {
        $this->errors[] = (object) [
            'status' => $exception->status,
            'message' => $exception->error,
        ];
    }

    /**
     * Get the response errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
