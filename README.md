# PHP send client for Mailtrap

### Plain Vanilla PHP

```php
use IgorVolgin\Mailtrap\Client;

// Instantiate Mailtrap API client
$client = new Client('your_mailtrap_api_token_here');

// send email
$client->send($fromEmail, $fromName, $toEmail, $toName, $subject, $text, $html);
```
