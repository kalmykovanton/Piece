```php
require __DIR__ . '/vendor/autoload.php';

use HttpExchange\Common\Stream;
use HttpExchange\Response\Response;
use Piece\ViewEngine;

$stream = new Stream('php://temp', 'wb+');
$response = new Response($stream);
$view = new ViewEngine($response);

$view->render('view.php', ['name' => 'Name', 'lastname' => 'Lastname', 'header' => 'Piece']);

@template('template.php'); // way to template
@embed; // where to insert view body
```