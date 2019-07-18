# Pixelart
PHP image to pixelart converter 

```php
require_once '../vendor/autoload.php';

$Pixelart = new \PS\Pixelart();
$Pixelart->setSquareSize(8)->pixelize('test_images/img.png', './test_images/test-image-pixel-8.png');
```
