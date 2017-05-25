<?php

try {
    require './lib/libs/Slim/Slim.php';
    require './lib/libs/config/database.php';
    require './lib/libs/classes/SystemUtils.php';
    require './lib/libs/classes/AppSessionHandler.php';
    require './lib/libs/constants/_CONSTANTS.php';

    \Slim\Slim::registerAutoloader();

    $App = new \Slim\Slim(array('mode' => 'production',
        'templates.path' => './views',
        'debug' => true,
        'routes.case_sensitive' => false));
    date_default_timezone_set('Africa/Kigali');

    //on 404 and 500 ...render custom pages
    $App->notFound(function () use ($App) {
        $App->render('404.html');
    });
    $App->error(function () use ($App) {
        $App->render('500.html');
    });

    session_start();

    if (!isset($_SESSION['username'])) {
        $Cookie = $App->getCookie('_admin');
        if (strlen($Cookie) >= 30) {
            $_SESSION = unserialize($Cookie);
        }
    }
} catch (Exception $e) {
    echo $e->getMessage() . ' .. ' . $e->getTraceAsString();
}

// init access headers
$App->response()->header('Access-Control-Allow-Origin: *');

$App->response()->header('Access-Control-Allow-Methods: GET, POST, DELETE');

$App->response()->header("Access-Control-Allow-Headers: X-Requested-With");

$App->response()->header("Access-Control-Allow-Headers: Content-Type");

$App->get('/login', function () use($App) {
    $App->render('login.html');
});

$App->get('/', function () use ($App) {
    if (isset($_SESSION['username'])) {
        $App->render('index.html', array('access' => $_SESSION['privilegie']));
    } else {
        $App->redirect('/login');
    }
});
$App->get('/dashboard', function () use ($App) {
    if (isset($_SESSION['username'])) {
        $App->render('index.html', array('access' => $_SESSION['privilegie']));
    } else {
        $App->redirect('/login');
    }
});
$App->get('/crime/setting', function () use ($App) {
    if (isset($_SESSION['username'])) {
        $App->render('settings.html', array('access' => $_SESSION['privilegie']));
    } else {
        $App->redirect('/login');
    }
});
$App->get('/crime/publish', function () use ($App) {
    if (isset($_SESSION['username'])) {
        $App->render('publishing.html', array('access' => $_SESSION['privilegie']));
    } else {
        $App->redirect('/login');
    }
});
$App->get('/crime/info', function () use ($App) {
    if (isset($_SESSION['username'])) {
        $App->render('information.html', array('access' => $_SESSION['privilegie']));
    } else {
        $App->redirect('/login');
    }
});

$App->get('/criminal/report/:id', function ($id) use ($App) {
    $filename = generateMemberCertificate($id, $App);
    if (isset($_SESSION['username'])) {
        $App->render('report.html', array('file' => $filename));
        return;
    }
});

$App->put('/admin/login', function () use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(loginAdmin($App), true));
});
$App->delete('/logout', function () use($App) {
    try {
        unset($_SESSION['username']);
        session_destroy();
        $App->deleteCookie('_admin');
        return true;
    } catch (Exception $e) {
        echo ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    $App->render('login.html');
    return false;
});

$App->post('/add/criminal', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(AddCrimeToSystem($App), true));
});
$App->post('/add/criminal/new', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(AddCrimeToSystemnew($App), true));
});
$App->put('/update/criminal/state', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(updateStateToSystem($App), true));
});
$App->put('/update/criminal/description', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(updateCrimeToSystem($App), true));
});
$App->post('/criminal/image', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(uploadImage($App), true));
});
$App->post('/add/criminal/wanted', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(AddCrimeToSystemwanted($App), true));
});
$App->post('/add/user', function () use($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(AddUserToSystem($App), true));
});
$App->get('/editting/user/:userid/:priv', function ($userid, $priv) use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(editUser($userid, $priv), true));
});
$App->get('/user/list', function () use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(RetrieveUsers(), true));
});
$App->get('/criminal/list', function () use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(RetrieveCriminals(), true));
});
$App->get('/criminal/list/export', function () use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(exportSpreadSheet(), true));
});
$App->get('/criminal/wanted', function () use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(Retrievewanted(), true));
});
$App->get('/criminal/history/:id', function ($id) use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(getCriminalHistory($id), true));
});
$App->delete('/delete/criminal/:id', function ($id) use ($App) {
    $App->response()->header('Content-Type', 'application/json');
    $App->response()->setBody(json_encode(deleteCriminalRecord($id), true));
});

$App->post('/admin/import/registration', function () use($App) {
    try {
        $App->response()->status(200);
        $App->response()->header('Content-Type', 'application/json');
        $App->response()->setBody(json_encode(importFromExcelSheets('registration'), true));
    } catch (Exception $e) {
        echo 'user', $e->getMessage() . ' trace ' . $e->getTraceAsString();
        $App->response()->setBody(json_encode(array('status' => 'failed', 'data' => 'error caught ' . $e->getMessage() . ' ... trace ' . $e->getTraceAsString()), true));
    }
});
$App->post('/admin/import/registration/:type', function ($type) use($App) {
    try {
        $App->response()->status(200);
        $App->response()->header('Content-Type', 'application/json');
        if (file_exists('./api/php/classes/Encoding.php')) {
            require_once './api/php/classes/Encoding.php';
        } else {
            $App->response()->setBody(json_encode(array('status' => 'failed', 'data' => 'Oops ...' . PHP_EOL), true));
            return;
        }
        $App->response()->setBody(json_encode(saveImportedRegistrations(json_decode(Encoding::toUTF8($App->request()->getBody()), true), $App->request()->getIp(), $type), true));
    } catch (Exception $e) {
        SystemUtils::logActivity('user', $_SESSION['app'], $e->getMessage() . ' trace ' . $e->getTraceAsString());
        $App->response()->setBody(json_encode(array('status' => 'failed', 'data' => 'error caught ' . $e->getMessage() . ' ... trace ' . $e->getTraceAsString()), true));
    }
});


$App->run();

/* ============================= my function ============================ */

function loginAdmin($App) {
    try {
        $responseData = array('status' => 'failed', 'data' => 'Sorry login failed Incorrect credentials.');
        $cred = json_decode($App->request->getBody(), true);
        $username = $cred['username'];
        $pass = $cred['password'];
        if (strlen($username) == 0) {
            $responseData ['data'] = 'Your email or phone number  number does not seem to be valid';
            return $responseData;
        }

        if (strlen($pass) < 8 || strlen($pass) > 32) {
            $responseData ['data'] = 'Change your password in order to be between 8 and 32 characters';
            return $responseData;
        }
        initDBConnection();
        $Personel = R::findOne("admins", "email=? AND password=?", array(trim($username), md5($pass)));
        if ($Personel) {
            $Personel->logindate = date("Y-m-d H:i:s");
            $id = R::store($Personel);
            if ($id >= 1) {
                $Info = $Personel->getProperties();
                unset($Personel);
                $_SESSION['username'] = $Info['username'];
                $_SESSION['privilegie'] = $Info['privilegie'];
                $_SESSION['id'] = $Info['id'];
                $App->setCookie('_admin', $_SESSION['username'], '30 days');
                $responseData['status'] = 'success';
                $responseData['data'] = '/';
            }
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function AddCrimeToSystem($App) {
    $responseData = array('status' => 'failed', 'data' => 'Unable to save this User ' . PHP_EOL);
    try {
        $staff = json_decode($App->request->getBody(), true);
        initDBConnection();
        $crime = R::findOne('criminals', 'idnumber=?', array($staff['idnumber']));
        if (!$crime) {
            $crime = R::dispense('criminals');
            $crime->fname = ucfirst(strtolower(trim($staff['fname'])));
            $crime->lname = ucfirst(strtolower(trim($staff['lname'])));
            $crime->gender = $staff['gender'];
            $crime->idnumber = trim($staff['idnumber']);
            $crime->country = trim($staff['country']);
            $crime->crimedate = trim($staff['crimedate']);
            $crime->dob = trim($staff['dob']);
            $crime->crimename = trim($staff['crimename']);
            $crime->description = trim($staff['description']);
            $crime->adminid = $_SESSION['id'];
            if (isset($_SESSION['crime_profile'])) {
                $crime->coverurl = $_SESSION['crime_profile'];
            } else {
                $crime->coverurl = '/criminals/not.jpg';
            }
            $crime->status = '0';
            $crime->release = '0';
            $id = R::store($crime);
            if ($id >= 1) {
                $responseData ['data'] = 'Record added.';
                $responseData ['status'] = 'success';
            } else {
                $responseData ['data'] = 'you can\'t add a record right now';
            }
        } else {
            $responseData ['data'] = 'This criminal is already in our system.you must update his case not adding a new one.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function AddCrimeToSystemnew($App) {
    $responseData = array('status' => 'failed', 'data' => 'Unable to save this User ' . PHP_EOL);
    try {
        $staff = json_decode($App->request->getBody(), true);
        initDBConnection();
        $states = R::findAll('criminals', 'idnumber=?', array($staff['idnumber']));
        foreach ($states as $state) {
            $state->release = '0';
            if (R::store($state) < 1) {
                return false;
            }
        }
        $crime = R::dispense('criminals');
        $crime->fname = ucfirst(strtolower(trim($staff['fname'])));
        $crime->lname = ucfirst(strtolower(trim($staff['lname'])));
        $crime->gender = $staff['gender'];
        $crime->idnumber = trim($staff['idnumber']);
        $crime->country = trim($staff['country']);
        $crime->crimedate = trim($staff['crimedate']);
        $crime->dob = trim($staff['dob']);
        $crime->crimename = trim($staff['crimename']);
        $crime->description = trim($staff['description']);
        $crime->coverurl = trim($staff['coverurl']);
        $crime->status = '0';
        $crime->release = '0';
        $crime->adminid = $_SESSION['id'];
        $id = R::store($crime);
        if ($id >= 1) {
            $responseData ['data'] = 'Record added.';
            $responseData ['status'] = 'success';
        } else {
            $responseData ['data'] = 'you can\'t add a record right now';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function updateStateToSystem($App) {
    $responseData = array('status' => 'failed', 'data' => 'Unable to save this User ' . PHP_EOL);
    try {
        $staff = json_decode($App->request->getBody(), true);
        initDBConnection();
        $crime = R::findOne('criminals', 'id=?', array($staff['id']));
        if ($crime) {
            $crime->release = '1';
            $crime->adminid = $_SESSION['id'];
            $id = R::store($crime);
            if ($id >= 1) {
                $responseData ['data'] = 'Criminal release';
                $responseData ['status'] = 'success';
            } else {
                $responseData ['data'] = 'you can\'t update a record right now';
            }
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function AddCrimeToSystemwanted($App) {

    $responseData = array('status' => 'failed', 'data' => 'Unable to save this User ' . PHP_EOL);
    try {
        $staff = json_decode($App->request->getBody(), true);
        initDBConnection();
        $crime = R::findOne('criminals', 'idnumber=?', array($staff['idnumber']));
        if (!$crime) {
            $crime = R::dispense('criminals');
            $crime->fname = ucfirst(strtolower(trim($staff['fname'])));
            $crime->lname = ucfirst(strtolower(trim($staff['lname'])));
            $crime->gender = $staff['gender'];
            $crime->idnumber = trim($staff['idnumber']);
            $crime->country = trim($staff['country']);
            $crime->crimedate = trim($staff['crimedate']);
            $crime->dob = trim($staff['dob']);
            $crime->crimename = trim($staff['name']);
            $crime->description = trim($staff['description']);
            $crime->adminid = $_SESSION['id'];
            if (isset($_SESSION['crime_profile'])) {
                $crime->coverurl = $_SESSION['crime_profile'];
            } else {
                $crime->coverurl = '/criminals/not.jpg';
            }
            $crime->status = '1';
            $id = R::store($crime);
            if ($id >= 1) {
                $responseData ['data'] = 'Record added.';
                $responseData ['status'] = 'success';
            } else {
                $responseData ['data'] = 'you can\'t add a record right now';
            }
        } else {
            $responseData ['data'] = 'This criminal is already in our system.you must update his case not adding a new one.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function updateCrimeToSystem($App) {
    $responseData = array('status' => 'failed', 'data' => 'Unable to save this User ' . PHP_EOL);
    try {
        $staff = json_decode($App->request->getBody(), true);
        initDBConnection();
        $crime = R::findOne('criminals', 'id=?', array($staff['id']));
        if ($crime) {
            $crime->description = trim($staff['desc']);
            $id = R::store($crime);
            if ($id >= 1) {
                $responseData ['data'] = 'Record updated.';
                $responseData ['status'] = 'success';
            } else {
                $responseData ['data'] = 'you can\'t update a record right now';
            }
        } else {
            $responseData ['data'] = 'You don\'t have permission';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function AddUserToSystem($App) {

    $responseData = array('status' => 'failed', 'data' => 'Unable to save this User ' . PHP_EOL);
    try {
        $staff = json_decode($App->request->getBody(), true);
        initDBConnection();
        $crime = R::findOne('admins', 'email=?', array($staff['email']));
        if (!$crime) {
            $crime = R::dispense('admins');
            $crime->fname = ucfirst(strtolower(trim($staff['fname'])));
            $crime->lname = ucfirst(strtolower(trim($staff['lname'])));
            $crime->gender = $staff['gender'];
            $crime->email = trim($staff['email']);
            $crime->username = trim($staff['username']);
            $crime->phone = trim($staff['phone']);
            $crime->status = '0';
            $crime->privilegie = trim($staff['privilege']);
            $crime->password = md5($staff['password']);
            $crime->regdate = date("Y-m-d H:i:s");
            $id = R::store($crime);
            if ($id >= 1) {
                $responseData ['data'] = 'user added.';
                $responseData ['status'] = 'success';
            } else {
                $responseData ['data'] = 'you can\'t add a record right now';
            }
        } else {
            $responseData ['data'] = 'This criminal is already in our system.you must update his case not adding a new one.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function RetrieveUsers() {
    $responseData = array('status' => 'failed', 'data' => "we couldn't retrieve users");
    try {
        initDBConnection();
        $responseData['data'] = R::getAll("SELECT *,case `privilegie` WHEN '1' THEN 'Simple admin' WHEN '2' THEN 'Super admin' END  AS prive,case `status` WHEN '0' THEN 'No activated' WHEN '1' THEN 'Activated' END  AS status from admins ORDER BY id DESC");
        if (sizeof($responseData['data']) >= 1) {
            $responseData['status'] = 'success';
        } else {
            $responseData['data'] = 'No user in the system.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function RetrieveCriminals() {
    $responseData = array('status' => 'failed', 'data' => "we couldn't retrieve criminals");
    try {
        initDBConnection();
        $responseData['data'] = R::getAll("SELECT *,COUNT(Id) AS count from criminals GROUP BY idnumber ORDER BY crimedate DESC");
        $p = 0;
        while ($p < count($responseData['data'])) {
            if ($responseData['data'][$p]['crimedate'] == NULL) {
                $responseData['data'][$p]['crimedate_'] = 'Never';
            } else {
                $responseData['data'][$p]['crimedate_'] = SystemUtils::nicetime($responseData['data'][$p]['crimedate']);
            }
            $p++;
        }
        if (sizeof($responseData['data']) >= 1) {
            $responseData['status'] = 'success';
        } else {
            $responseData['data'] = 'No criminals in the system.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function Retrievewanted() {
    $responseData = array('status' => 'failed', 'data' => "we couldn't retrieve criminals");
    try {
        initDBConnection();
        $responseData['data'] = R::getrow("SELECT * from criminals WHERE status='1' ORDER BY id DESC");
        if (sizeof($responseData['data']) >= 1) {
            $responseData['status'] = 'success';
        } else {
            $responseData['data'] = 'No wanted in the system.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function getCriminalHistory($id) {
    $responseData = array('status' => 'failed', 'data' => "we couldn't retrieve criminal history");
    try {
        initDBConnection();
        $responseData['data'] = R::getAll("SELECT crimename,crimedate,id from criminals WHERE idnumber=? ORDER BY crimedate ASC", array($id));
        if (sizeof($responseData['data']) >= 1) {
            $responseData['status'] = 'success';
        } else {
            $responseData['data'] = 'No history for this criminal.';
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function editUser($userid, $priv) {
    $responseData = array('status' => 'failed', 'data' => "Sorry,user not updated");
    try {
        initDBConnection();
        $Bean = R::findOne('admins', 'id=?', array($userid));
        if ($Bean) {
            $Bean->privilegie = $priv;
            $id = R::store($Bean);
            if ($id >= 1) {
                $responseData['status'] = 'success';
                $responseData ['data'] = 'User has been updated.';
                return $responseData;
            }
        }
    } catch (Exception $e) {
        $responseData['data'] = ' error in  ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
    }
    return $responseData;
}

function deleteCriminalRecord($id) {
    $res = array('status' => 'failed', 'data' => 'Unable to delete criminal' . PHP_EOL);
    if (!initDBConnection()) {
        $res['data'] .='Unable to connect to database.' . PHP_EOL;
        return $res;
    }
    if (intval(authorize()) == 2) {
        $bean = R::findOne('criminals', 'id=?', array(intval($id)));
        if ($bean) {
            R::trash($bean);
            $res['status'] = 'success';
            $res['data'] = 'this crime has been removed';
            return $res;
        } else {
            $res['data'].='Criminal not found.';
            return $res;
        }
    } else {
        $res['data'] = 'you don\'t have permission to delete this crime to this criminal';
    }
    return $res;
}

function authorize() {
    initDBConnection();
    return R::getCell("SELECT privilegie from admins WHERE username=?", array($_SESSION['username']));
}

function initDBConnection() {
    try {
        if (!file_exists('./lib/libs/RedBean/rb.php')) {
            return false;
        } else {
            require_once './lib/libs/RedBean/rb.php';
        }
        try {
            R::selectDatabase('default');
            return true;
        } catch (Exception $ex) {
            $ex->getFile();
            R::setup(DB_HOST_DB, DB_USER, DB_PASSWORD);
            R::setAutoResolve(TRUE);
            R::freeze(TRUE);
            return true;
        }
    } catch (Exception $e) {
        SystemUtils::logActivity('system', 'failed to connect to  db with exception ' . $e->getMessage() . ' ... trace ' . $e->getTraceAsString());
    }
    return false;
}

//============================*********************THIS IS TO UPLOAD FILES******************===============================

function uploadImage() {
    try {
        $response = array('status' => 'failed', 'data' => 'image not uploaded');

        $DestinationFolder = './profiles/' . date("Ymd") . '/';
        $accepted_imagetypes = array(
            'image/jpeg',
            'image/jpg',
            'image/jpeg',
            'image/x-png',
            'image/png'
        );

        if (isset($_FILES ["upl"])) {
            if (isset($_FILES ["upl"]['name']) && is_array($_FILES ["upl"]['name'])) {
                $myFile = array('name' => $_FILES ["upl"]['name'][0],
                    'tmp_name' => $_FILES ["upl"]['tmp_name'][0],
                    'type' => $_FILES ["upl"]['type'][0],
                    'size' => $_FILES ["upl"]['size'][0],
                    'error' => $_FILES ["upl"]['error'][0]
                ); //only single&serial uploads are allowed
            } else {
                $myFile = $_FILES ["upl"];
            }
        }

        if ($myFile ["error"] !== 0) {
            if (is_string($myFile ["tmp_name"])) {
                unlink($myFile ["tmp_name"]);
            }
            echo 'user', $_SESSION['app'], 'failed to upload with  upload error ' . $myFile ["error"];
            return -503;
        }
        $type = $myFile ["type"];
        $size = $myFile ["size"];

        if (strlen($type) < 3) {
            $type = SystemUtils::mime_file_content_type($myFile ["tmp_name"]);
        }

        if (!in_array(trim($type), $accepted_imagetypes)) {
            unlink($myFile ["tmp_name"]);
            return - 300;
        } else {
            if ($size < 1024) {
                $size = filesize($myFile ["tmp_name"]);
            }
            if ($size >= 10485760) {
                unlink($myFile ["tmp_name"]);
                return -1000;
            }

            // ensure a safe filename
            $name = preg_replace("/[^A-Z0-9._-]/i", "_", $myFile ["name"]);
            if (strlen($name) >= 100) {
                $name = substr($name, -100);
            }

            $DestinationFolder = './profiles/' . date("Ymd") . '/';
            if (!is_dir($DestinationFolder)) {
                if (!mkdir($DestinationFolder, 0777, true)) {
                    unlink($myFile ["tmp_name"]);
                    return - 100;
                }
            }

            // preserve file from temporary directory
            $saveFile = move_uploaded_file($myFile ["tmp_name"], $DestinationFolder . $name);
            if (!$saveFile) {
                unlink($myFile ["tmp_name"]);
                return - 400;
            } else {
                $_SESSION['crime_profile'] = $DestinationFolder . $name;
                $response['status'] = 'success';
                $response['date'] = 'image uploaded.';
                return $response;
            }
        }
    } catch (Exception $e) {
        echo ' failed to upload ....trace' . $e->getTraceAsString() . ' message' . $e->getMessage() . ' line' . $e->getLine();
        return - 700;
    }
}

//============================*********************THIS IS FOR PDF GENERATOR******************===============================

function generateMemberCertificate($member, $App) {
    try {
        if ($App == null) {
            $App = \Slim\Slim::getInstance();
        }
        if (@file_exists('./lib/libs/tcpdf/tcpdf.php')) {
            require_once('./lib/libs/tcpdf/tcpdf.php');
        } else {
            $App->response()->status(200);
            $App->response()->header('Content-Type', 'text/html');
            $App->response()->setBody('<html><title>Aaah</title><p style="color:#119FBD;"><font size="10"> Failed to generate certificate\'s PDF. <br>Unable to find tcpdf library</p></html>');
            return;
        }
        initDBConnection();
        $i = 0;
        $crime = "";
        $descr = "";
        $Info = R::getRow("SELECT * FROM criminals WHERE id=?", array($member));
        $crimes = R::getAll("SELECT crimename,(SELECT fname FROM admins WHERE id=adminid LIMIT 1) AS users,description FROM criminals WHERE idnumber=?", array($Info['idnumber']));
        while ($i < sizeof($crimes)) {
            $crime .= $crimes[$i]['crimename'] . ' added by <b>' . $crimes[$i]['users'] . '</b><br>';
            $descr .= $crimes[$i]['description'] . ' ';
            $i++;
        }
        if (!isset($Info['id'])) {
            $App->response()->status(200);
            $App->response()->header('Content-Type', 'text/html');
            $App->response()->setBody('<html><title>No shares</title><p style="color:#119FBD;"><font size="10"> Failed to generate certificate\'s PDF. <br>Unable to find member info. </p></html>');
            return;
        }
        $filename = '/_reportcrime.pdf';
        $Info['logo'] = './img/Police.png';
        $Info['drapeau'] = './img/classified-stamp.png';

        // create new PDF document
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'localhost';
        }
        // set document information
        $pdf->SetCreator("peter");
        $pdf->SetAuthor("peter damien");
        $pdf->SetTitle($Info['fname'] . ' ' . $Info['lname'] . ' report');
        $pdf->SetSubject('report of ' . $Info['fname'] . ' ' . $Info['lname']);
        $pdf->SetKeywords('nfnv,new faces new voices, rwanda,member,certificate,' . $Info['fname'] . $Info['lname'] . ',' . $_SERVER['SERVER_NAME']);

        // remove default header/footer
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // add a page
        $pdf->AddPage();
        $html_member_cert = '<p><br></p>';
        $pdf->writeHTML($html_member_cert, true, false, true, false, 'C');
        if (file_exists($Info['logo'])) {
            $pdf->Image($Info['logo'], '', '', 32, 30, pathinfo($Info['logo'], PATHINFO_EXTENSION), '', 'T', true, 100, 'L', false, false, 0, false, false, false);
        }
        $pdf->Image($Info['coverurl'], '', '', 32, 30, pathinfo($Info['coverurl'], PATHINFO_EXTENSION), '', 'T', true, 100, 'R', false, false, 0, false, false, false);
        $html_member_cert = '<p><br></p>';
        $pdf->writeHTML($html_member_cert, true, false, true, false, 'C');
        $html_member_cert = '<p><br></p>';
        $pdf->writeHTML($html_member_cert, true, false, true, false, 'C');
        $html_member_cert = '<h1 style="color:blue;"><font size="23">**CRIMINAL DOC**</h1>';
        $pdf->writeHTML($html_member_cert, true, false, true, false, 'C');
        $html_member_cert = '<p style="color:#AAA;font-size:13;"> This is a document of <b>' . $Info['fname'] . ' ' . $Info['lname'] . '</b><br>';
        $html_member_cert .=' <p style="font-size:10;"><i>' . $Info['description'] . $descr . ' </i> <p/>.<br>';
        $pdf->writeHTML($html_member_cert, true, false, true, false, 'L');
        $html_member_cert = ' <p style="font-size:10;color:#AAA;">' . $crime . ' <p/>.<br><br>';
        $pdf->writeHTML($html_member_cert, true, false, true, false, 'L');
        $pdf->Image($Info['drapeau'], '', '', 40, 20, pathinfo($Info['drapeau'], PATHINFO_EXTENSION), '', 'B', true, 300, 'C', false, false, 0, false, false, false);
        $pdf->Output($_SERVER['DOCUMENT_ROOT'] . $filename, 'F');
        $pdf->Close();
        return $filename;
    } catch (Exception $e) {
        $App->response()->status(200);
        $App->response()->header('Content-Type', 'text/html');
        $App->response()->setBody('<html><title>Oops</title><p style="color:#119FBD;"><font size="10"> Failed to generate report\'s PDF. <br>An exception was caught ' . $e->getMessage() . ' trace ' . $e->getTraceAsString() . ' </p></html>');
        SystemUtils::logActivity('user', 'app', 'failed to criminal report ' . $e->getMessage() . ' trace ' . $e->getTraceAsString());
    }
}

//============================*********************THIS IS FOR EXCEL EXPORT******************===============================

function exportSpreadSheet() {
    initDBConnection();
    try {
        exportRegistrations(R::getAll("SELECT fname,"
                        . "lname,"
                        . "idnumber,"
                        . "gender,"
                        . "country,"
                        . "dob,"
                        . "crimedate,"
                        . "crimename"
                        . " FROM criminals "));
    } catch (Exception $e) {
        echo 'exc in exportSpreadSheet() ...  An application exception caught at ' . $e->getTraceAsString() . ' message ' . $e->getMessage();
    }
}

function exportRegistrations($individuals) {

    if (file_exists('./lib/libs/classes/PHPExcel.php')) {
        require_once './lib/libs/classes/PHPExcel.php';
    } else {
        echo 'Application Library not found';
        return;
    }

    $xls = new PHPExcel();
// Set document properties
    $xls->getProperties()->setCreator("lc damy")
            ->setLastModifiedBy("lc damy")
            ->setTitle("criminal list " . date("Y-m-d"))
            ->setSubject("lc damy")
            ->setDescription("This XLS form has been exported from AIDS")
            ->setKeywords("Rwanda, Women in Business,New Faces New Voices")
            ->setCategory("members  exported for offline view");


    // Rename worksheet
// Add title data
    $xls->setActiveSheetIndex(0)
            ->setCellValue('A1', 'First Name')
            ->setCellValue('B1', 'Last Name')
            ->setCellValue('C1', 'Nationa ID')
            ->setCellValue('D1', 'Sex')
            ->setCellValue('E1', 'Country')
            ->setCellValue('F1', 'dob')
            ->setCellValue('G1', 'Crime date')
            ->setCellValue('H1', 'Crime type');

    $xls->getActiveSheet()->getColumnDimension('A')->setWidth(32);
    $xls->getActiveSheet()->getColumnDimension('B')->setWidth(32);
    $xls->getActiveSheet()->getColumnDimension('C')->setWidth(40);
    $xls->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $xls->getActiveSheet()->getColumnDimension('E')->setWidth(40);
    $xls->getActiveSheet()->getColumnDimension('F')->setWidth(24);
    $xls->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $xls->getActiveSheet()->getColumnDimension('H')->setWidth(15);


    $xls->getActiveSheet()->setTitle('Individuals');
    $xls->setActiveSheetIndex(0);
    $xls->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
    $xls->getActiveSheet()->getStyle(1)->applyFromArray(array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => 'FFFFFF'),
            'size' => 13,
            'name' => 'Verdana'
    )));

    $xls->getActiveSheet()->getStyle(1)->applyFromArray(array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '1C5160')
        )
            )
    );

    if (sizeof($individuals) >= 1) {
        $row = 1;
        for ($i = 0; $i < sizeof($individuals); $i++) {
            $row++;
            $xls->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $individuals[$i]['fname'])
                    ->setCellValue('B' . $row, $individuals[$i]['lname'])
                    ->setCellValue('C' . $row, $individuals[$i]['idnumber'])
                    ->setCellValue('D' . $row, $individuals[$i]['gender'])
                    ->setCellValue('E' . $row, $individuals[$i]['country'])
                    ->setCellValue('F' . $row, $individuals[$i]['dob'])
                    ->setCellValue('G' . $row, $individuals[$i]['crimedate'])
                    ->setCellValue('H' . $row, $individuals[$i]['crimename']);
        }
    } else {
        $xls->setActiveSheetIndex(0)->setCellValue('A4', 'No registrations of individuals with membership of status');
    }
//
    $xls->createSheet();
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $xls->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . date("Y-m-d_H") . '_nfnv__registrants.xlsx"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');
    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
    ob_end_clean();
    ob_end_flush();
    $objWriter->save('php://output');
}

//============================*********************THIS IS FOR IMPORT EXPORT******************===============================

function importFromExcelSheets($context) {
    $res = array('status' => 'failed', 'data' => 'Unable to import ' . $context . PHP_EOL);
    try {
        $accepted_mimes = array('text/csv',
            'application/vnd.ms-excel',
            'application/msexcel',
            'application/x-msexcel',
            'application/x-ms-excel',
            'application/x-excel',
            'application/x-dos_ms_excel',
            'application/xls',
            'application/x-xls',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.spreadsheet-template',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/msexcel',
            'application/x-msexcel',
            'application/x-ms-excel',
            'application/x-excel',
            'application/x-dos_ms_excel',
            'application/xls',
            'application/x-xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if (!empty($_FILES ["upl"])) {
            $myFile = $_FILES ["upl"];
            if ($myFile ["error"] !== 0) {
                unlink($myFile ["tmp_name"]);
                $res['data'].='An error occured while uploading spreadsheet ' . $myFile ["error"];
                return $res;
            }
            $type = $myFile ["type"];
            $size = $myFile ["size"];
            if (strlen($type) < 3) {
                $type = mime_file_content_type($myFile ["tmp_name"]);
            }
            if (!in_array(trim($type), $accepted_mimes)) {
                unlink($myFile ["tmp_name"]);
                $res['data'].='The uploaded file is not a supported sheet file .i.e ' . $type;
                return $res;
            } else {
                if ($size < 1024) {
                    $size = filesize($myFile ["tmp_name"]);
                }
                if ($size >= 10485760) { //no more than 10 MB
                    unlink($myFile ["tmp_name"]);
                    $res['data'].='The uploaded file is not too large .i.e ' . $size . ' bytes';
                    return $res;
                }
            }
            $name = './' . $context . '_' . date("Ymd") . '_' . preg_replace("/[^A-Z0-9._-]/i", "_", $myFile ["name"]);
            move_uploaded_file($myFile ["tmp_name"], $name);
            if (!file_exists($name)) {
                $res['data'].='unable to locate file at ' . $name;
                return $res;
            } else {
                $res['file'] = 'file is at ' . $name;
            }
            
            $res = importRegistration($name, 'return', 10);
            if ($res['status'] == 'success') {
                if (initDBConnection()) {
                    if (createTableImportsRecord(date("Ym"))) {
                        saveImportedRecord(date("Ym"), $context, serialize($res['data']));
                    }
                }
            }
        } else {
            $res['data'].='The upload was empty...please change your browser';
            return $res;
        }
    } catch (Exception $e) {
        echo 'user The app  ' . $context . ' failed to....trace' . $e->getTraceAsString() . ' message' . $e->getMessage() . ' line' . $e->getLine();
        $res['data'] = 'An error occurred ... ' . $e->getMessage();
    }
    return $res;
}

function importRegistration($source, $destination, $maxCol) {
    $res = array('status' => 'failed', 'data' => 'Unable to process spreadsheet ... ' . PHP_EOL);
    try {
        if (@file_exists('./lib/libs/classes/excel/excel_reader.php')) {
            require_once('./lib/libs/classes/excel/excel_reader.php');
        } else {
            $res['data'] = 'Unable to load excel reader library';
            return $res;
        }
        if (@file_exists('./lib/libs/classes/excel/SpreadsheetReader.php')) {
            require_once('./lib/libs/classes/excel/SpreadsheetReader.php');
        } else {
            $res['data'] = 'Unable to load spreadsheet reader library';
            return $res;
        }
        if (file_exists('./lib/libs/classes/enconding.php')) {
            require_once './lib/libs/classes/enconding.php';
        } else {
            $res['data'] = 'Unable to load Encoding library';
            return $res;
        }
        $Spreadsheet = new SpreadsheetReader($source);
        $Sheets = $Spreadsheet->Sheets();
        $res['data'] = array();
        foreach ($Sheets as $Index => $Name) {
            array_push($res['data'], array('sheet' => $Name, 'rows' => array()));
            $Spreadsheet->ChangeSheet($Index);
            $row_position = 0;
            $row_id = 1;
            foreach ($Spreadsheet as $Key => $Row) {
                $cols = array();
                if (isset($Row[2]) && strlen(SystemUtils::trimToNumeric($Row[2])) >= 4) {
                    if ($maxCol > sizeof($Row)) {
                        $maxCol = sizeof($Row);
                    }
                    $Row[0] = $row_id;
                    for ($i = 0; $i < $maxCol; $i++) {
                        array_push($cols, trim(Encoding::toUTF8($Row[$i])));
                    }
                    //$cols[$maxCol] = 0;
                    $row_id++;
                    $row_position++;
                    unset($Row);
                    array_push($res['data'][$Index]['rows'], $cols);
                }
            }
            array_unshift($res['data'][$Index]['rows'], array('No', 'First Name', 'Last Name','Id Number', 'Sex', 'Country', 'Dob', 'Crime date', 'Crime type'));
        }
        if (isset($res['data'][0]['rows']) && sizeof($res['data'][0]['rows']) >= 1) {
            $res['status'] = 'success';
        }
        if ('return' == $destination) {
            return $res;
        } else {
            if (file_put_contents($destination, json_encode($res['data'], true), FILE_APPEND)) {
                $res['status'] = 'success';
                $res['data'] = 'The excel data has been exported to json and saved at ' . $destination;
            } else {
                $res['status'] = 'failed';
                $res['data'] = 'The excel data has been exported to json but not saved at ' . $destination;
            }
        }
    } catch (Exception $e) {
        $res['data'] = 'Unable to process spreadsheet' . PHP_EOL . ' An exception ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
        SystemUtils::logActivity('user', $_SESSION['app'], 'failed to import Registration with excel_reader ' . $e->getMessage() . ' trace ' . $e->getTraceAsString());
    }
    return $res;
}

function createTableImportsRecord($postfix) {
    try {
        R::exec("CREATE TABLE IF NOT EXISTS `imports" . $postfix . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userkey` varchar(255) NOT NULL,
  `context` varchar(25) NOT NULL,
  `import` longtext,
  `import_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        return true;
    } catch (Exception $e) {
        SystemUtils::logActivity('user', $_SESSION['app'], 'failed to createTableImportsRecord ' . $e->getMessage() . ' trace ' . $e->getTraceAsString());
    }
    return false;
}

function saveImportedRecord($postfix, $context, $data) {
    $bean = R::dispense('imports' . $postfix);
    $bean->context = $context;
    $bean->import = $data;
    $bean->import_date = date("Y-m-d H:i:s");
    return R::store($bean);
}


function processImportedRegistrationSheet($data, $ip, $type) {

    $added = sizeof($data);
    if ($added < 1) {
        return array('status' => 'failed', 'added' => 0);
    }
    $i = 0;
    $count = $added;
    while ($i < $count) {
        try {
            if (intval(SystemUtils::trimToNumeric($data[$i][0])) >= 1) { //No. mendatory to avoid headers
                $data[$i][1] = explode(' ', $data[$i][1]); //names
                $data[$i][1][0] = ucfirst(strtolower(trim($data[$i][1][0]))); //first name
                if (!isset($data[$i][1][1])) {
                    $data[$i][1][1] = '  ';
                } else {
                    $data[$i][1][1] = ucfirst(strtolower(trim($data[$i][1][1])));  //last name
                }
                $data[$i][2] = trim($data[$i][2]); //ID / passport
                if ($type == 'business' && strlen($data[$i][2]) != 9) {
                    $added--;
                    $i++;
                    continue;
                }
                if (strlen($data[$i][3]) < 10) { //phone --unique key
                    $data[$i][3] = '250700' . $data[$i][0] . rand(10000, 99999);
                } else {
                    $data[$i][3] = SystemUtils::trimToNumeric($data[$i][3]);
                }
                $email = $data[$i][3] . '@nfnv.rw';
                $data[$i][3] = SystemUtils::formatPhoneNumber($data[$i][3]);
                $data[$i][4] = strtolower(trim($data[$i][4])); //Province
                if (strpos($data[$i][4], 'rengerazuba') || strpos($data[$i][4], 'west')) {
                    $data[$i][4] = 'Iburengerazuba';
                } else if (strpos($data[$i][4], 'rasirarazuba') || strpos($data[$i][4], 'ast')) {
                    $data[$i][4] = 'Iburasirazuba';
                } else if (strpos($data[$i][4], 'ruguru') || strpos($data[$i][4], 'north')) {
                    $data[$i][4] = 'Amajyaruguru';
                } else if (strpos($data[$i][4], 'epfo') || strpos($data[$i][4], 'south')) {
                    $data[$i][4] = 'Amajyepfo';
                }
                $data[$i][4] = ucfirst($data[$i][4]);
                $data[$i][5] = ucfirst(strtolower(trim($data[$i][5]))); //District
                $data[$i][6] = ucfirst(strtolower(trim($data[$i][6]))); //Sector
                $data[$i][7] = ucfirst(strtolower(trim($data[$i][7]))); //Cell
                $data[$i][8] = ucfirst(strtolower(trim($data[$i][8]))); //Profession Text
                $data[$i][9] = SystemUtils::trimToNumeric($data[$i][9]); //Category
                if (!isset($data[$i][9])) {
                    $data[$i][9] = 0;
                }
                $Bean = R::findOne('registrations', 'registrant_id=? OR  SUBSTRING(registrant_phone,-14)=?', array($data[$i][2], substr(SystemUtils::formatPhoneNumber($data[$i][3]), -14)));
                if ($Bean) {
                    $Bean->registrant_firstname = $data[$i][1][0];
                    $Bean->registrant_lastname = $data[$i][1][1];
                    $Bean->registrant_id = $data[$i][2];
                    $Bean->registrant_phone = $data[$i][3];
                    $Bean->registrant_province = $data[$i][4];
                    $Bean->registrant_district = $data[$i][5];
                    $Bean->registrant_sector = $data[$i][6];
                    $Bean->registrant_cell = $data[$i][7];
                    $Bean->assigned_income = $data[$i][9];
                    $Bean->profession_text = $data[$i][8];
                    $Bean->datelastlogin = date("Y-m-d H:i:s");
                    $Bean->ipaccess = $ip;
                    if (R::store($Bean) < 1) {
                        $added--;
                    }
                } else {
                    $pin = '-' . rand(1000000, 9999999);
                    $Bean = R::dispense('registrations');
                    $Bean->registrant_key = SystemUtils::generateKey(250, false, true);
                    $Bean->registrant_pin = $pin;
                    $Bean->registrant_firstname = $data[$i][1][0];
                    $Bean->registrant_lastname = $data[$i][1][1];
                    $Bean->registrant_id = $data[$i][2];
                    $Bean->registrant_type = $type;
                    $Bean->registrant_email = $email;
                    $Bean->registrant_ref = $_SESSION['admin_fname'] . ' ' . $_SESSION['admin_lname'];
                    $Bean->registrant_phone = $data[$i][3];
                    $Bean->registrant_province = $data[$i][4];
                    $Bean->registrant_district = $data[$i][5];
                    $Bean->registrant_sector = $data[$i][6];
                    $Bean->registrant_cell = $data[$i][7];
                    $Bean->assigned_income = $data[$i][9];
                    $Bean->profession_text = $data[$i][8];
                    $Bean->dateregistered = date("Y-m-d H:i:s");
                    $Bean->datelastlogin = date("Y-m-d H:i:s");
                    $Bean->ipaccess = $ip;
                    if (R::store($Bean) < 1) {
                        $added--;
                    }
                }
            } else {
                $added--;
            }
        } catch (Exception $e) {
            $added--;
            SystemUtils::logActivity('user', $_SESSION['app'], 'failed to process Imported RegistrationSheet with msg ' . $e->getMessage() . ' trace ' . $e->getTraceAsString());
        }
        $i++;
    }
    if ($added < 1) {
        return array('status' => 'failed', 'added' => 0);
    }
    return array('status' => 'success', 'added' => $added);
}

function saveImportedRegistrations($data, $ip, $type) {
    $res = array('status' => 'failed', 'data' => 'Unable to process data ... ' . PHP_EOL);
    if (!in_array($type, array('individual', 'business'))) {
        $res['data'] .= ' Only individual and business registrations can be imported.';
        return $res;
    }
    try {
        if (!initDBConnection()) {
            $res['data'] .= ' Database connection failed.';
            return $res;
        }
        if (!isset($data[0]['rows'])) {
            $res['data'] .= ' Data format is bad.';
            return $res;
        }
        R::begin();
        $added = 0;
        for ($i = 0; $i < sizeof($data); $i++) {
            $sheet = processImportedRegistrationSheet($data[$i]['rows'], $ip, $type);
            if ($sheet['status'] == 'success') {
                $added+= $sheet['added'];
            }
        }
        if ($added > 1) {
            R::commit();
            $res['data'] = $added . ' registrants have been added to NFNV registration directory';
            $res['status'] = 'success';
            fixOldPins();
        } else {
            $res['data'] = ' No registrant has been added to NFNV registration directory';
        }
    } catch (Exception $e) {
        $res['data'] = 'Unable to process save imported registrations  ' . PHP_EOL . ' An exception ' . $e->getMessage() . ' trace ' . $e->getTraceAsString();
        SystemUtils::logActivity('user', $_SESSION['app'], 'failed to save Imported Registrations with excel_reader ' . $e->getMessage() . ' trace ' . $e->getTraceAsString());
    }
    return $res;
}
