<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/db-connect.php';

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

//set timezone
date_default_timezone_set("America/Chicago");

// Setup the application
$app = new Application();
$app->register(new TwigServiceProvider, array(
    'twig.path' => __DIR__ . '/templates',
));

// Setup the database
$app['db.table'] = DB_TABLE;
$app['db.dsn'] = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST;
$app['db'] = $app->share(function ($app) {
    return new PDO($app['db.dsn'], DB_USER, DB_PASSWORD);
});

// Handle the index page
$app->match('/', function () use ($app) {
    return $app['twig']->render('index.twig', array());
});

// Handle the add page
$app->match('/add', function (Request $request) use ($app) {
    $alert = null;
    // If the form was submitted, process the input
    if ('POST' == $request->getMethod()) {
        try {
            // Make sure the photo was uploaded without error
           // $message = $request->request->get('thoughtMessage');
            $name = $request->request->get('name');
            $date1 = date("Y-m-d h:i A");
            $location = 'tbd';
            if ($name && $date1 && $location && strlen($name) < 64) {
                // Save the attendance record to the database
                $sql = "INSERT INTO {$app['db.table']} (date1, name, location) VALUES (:date1, :name, :location)";
                $query = $app['db']->prepare($sql);

                $data = array(
                    ':date1'  => $date1,
                    ':name' => $name,
                    ':location' => $location
                );

                if (!$query->execute($data)) {
                    var_dump($query);
                    print_r($query);
                    throw new \RuntimeException('Saving your thought to the database failed.'. ' ' . $query->errorInfo()[0]);
                }
            } else {
                throw new \InvalidArgumentException('Sorry, The format of your thought was not valid.');
            }

            // Display a success message
            $alert = array('type' => 'success', 'message' => 'Attendance recorded');
        } catch (Exception $e) {
            // Display an error message
            $alert = array('type' => 'error', 'message' => $e->getMessage());
        }
    }

    return $app['twig']->render('add.twig', array(
        'alert' => $alert,
    ));
});

$app->match('/view', function () use ($app){

    $query = $app['db']->prepare("SELECT date1, name, location FROM {$app['db.table']}");
    $list = $query->execute() ? $query->fetchAll(PDO::FETCH_ASSOC) : array();

    return $app['twig']->render('view.twig', array(
        'title'    => 'Attendance Record',
        'list' => $list,
    ));

});

$app->run();
