# SparkPost API Yii2 Mailer

## Installation

Edit composer.json. Add
```
    {
      "type": "vcs",
      "url": "https://github.com/connectionsbv/yii2-sparkpost"
    }
```
to the repositories section and add
```
    "connectionsbv/yii2-sparkpost": "*"
```
to the required section.

## Usage

Add the following code in your application configuration:
```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'connectionsbv\sparkpost\Mailer',
            'token' => 'YOUR_TOKEN',
        ],
    ],
];
```

### Send an email

You can then send an email as follows:
```php
Yii::$app->mailer->compose()
    ->setFrom('from@domain.com')
    ->setReturnPath('from@bounces.domain.com')
    ->setTo($to)
    ->setSubject($from)
    ->send();
```