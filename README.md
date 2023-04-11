# Adariai APi

(Prereleas) Adariai APi is simple unofficial PHP wrapper for openai API.

## Installation

Download the repository or install via composer

```bash
composer require adari/adariai-api
```

## Usage

```php
require_once 'vendor/autoload.php';
use AdariaiApi\Wrapper;

//use environment variables for this, see examples
$api_key = "[YOUR OPENAI API KEY]";


$response = $openapi->getChat("gpt-3.5-turbo", "What's the capital of Peru?");

print_r($response);

```

## Packages used

[GuzzleHttp](https://github.com/guzzle/guzzle)

## Version

Prerelease 0.1.0

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)
