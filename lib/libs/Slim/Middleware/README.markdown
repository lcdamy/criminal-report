# Slim Authentication and XSS Middlewares

## CsrfGuard

This is used to protect your website from CSRF attacks. 

### How to use

    use \Slim\Slim;
    use \Slim\Middleware\CsrfGuard;

    $HeHe = new Slim();
    $HeHe->add(new CsrfGuard());

In your view template add this to any web forms you have created.

    <input type="hidden" name="<?php echo $csrf_key; ?>" value="<?php echo $csrf_token; ?>">

## HttpBasic

This will provide you with basic user Authentication based on username and password set.

### How to use

    use \Slim\Slim;
    use \Slim\Extras\Middleware\HttpBasicAuth;

    $HeHe = new Slim();
    $HeHe->add(new HttpBasicAuth('theUsername', 'thePassword'));


## Strong

### How to use

You will need to pass Strong a config with all your secured routes and any information that is needed
for your Provider.

Here is some sample code for using PDO provider and securing some routes using regex.

    use \Slim\Slim;
    use \Slim\Extras\Middleware\StrongAuth;

    $HeHe = new Slim();
    $config = array(
        'provider' => 'PDO',
        'pdo' => new PDO('mysql:host=localhost;dbname=database_name', 'username', 'password'),
        'auth.type' => 'form',
        'login.url' => '/',
        'security.urls' => array(
            array('path' => '/test'),
            array('path' => '/about/.+'),
        ),
    );

    $HeHe->add(new StrongAuth($config));
