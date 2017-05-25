var app = angular.module('So_Pharma', ['So_Pharma.services', 'angularUtils.directives.dirPagination']);
//================================  this is the control of the login page   ==================================//

app.controller('login_ctrl', function ($scope, Data, $window) {

    $scope.signin = function (sign) {

        $scope.username = _('username').value;
        $scope.password = _('password').value;
        if ($scope.username == "" || $scope.password == "") {
            _('isa_error').style.display = "block";
            $scope.messageIN = "Please fill in the form correctly";
            setTimeout(hidden_error_sign_in, 3000);
            return false;
        }
        _('isa_success').style.display = "block";
        Data.put('/admin/login', {
            dashData: sign
        }).success(function (response) {
            if (response.status === "success") {
                $window.location.href = response.data;
            } else {
                _('isa_success').style.display = "none";
                _('isa_error').style.display = "block";
                $scope.messageIN = response.data;
                setTimeout(hidden_error_sign_in, 5000);
            }
        }).error(function (err) {
            console.log(err);
        });
    };
});

//================================  this is the control of the dashboard page  ==================================//\

app.controller('index_ctrl', function ($scope, Data, $window) {
    $scope.crimeId = '0';
    $scope.session_dash = function (idbtn, loading) {
        $scope.Listingcriminals();
        $scope.uploadAdminProfile(idbtn, loading);
    };
    $scope.logout = function () {
        Data.delete('/logout')
                .success(function (response) {
                    location.reload();
                }).error(function (err) {
            console.log('connection failed.');
        });
    };
    $scope.savecrime = function (sign) {
        Data.post('/add/criminal', {
            dashData: sign
        }).success(function (response) {
            if (response.status == "success") {
                _('isa_successnew').style.display = "block";
                _('isa_errornew').style.display = "none";
                _('isa_success_messagenew').innerHTML = response.data;
                $scope.Listingcriminals();
                setTimeout(hidden_error, 5000);
            } else {
                _('isa_successnew').style.display = "none";
                _('isa_errornew').style.display = "block";
                _('isa_error_messagenew').innerHTML = response.data;
            }
        }).error(function (err) {
            console.log(err);
        });
    };
    $scope.savecrimeup = function (info, crimenamenew, crimedatenew, descriptionnew) {
        info.crimedate = crimedatenew;
        info.crimename = crimenamenew;
        info.description = descriptionnew;
        Data.post('/add/criminal/new', {
            dashData: info
        }).success(function (response) {
            if (response.status == "success") {
                _('isa_success').style.display = "block";
                _('isa_error').style.display = "none";
                _('isa_success_message').innerHTML = response.data;
                $scope.Listingcriminals();
                setTimeout(hidden_error, 5000);
            } else {
                _('isa_success').style.display = "none";
                _('isa_error').style.display = "block";
                _('isa_error_message').innerHTML = response.data;
            }
        }).error(function (err) {
            console.log(err);
        });
    };

    $scope.Listingcriminals = function () {
        var i = 0;
        Data.get('/criminal/list')
                .success(function (response) {
                    $scope.criminales = response.data;
                }).error(function (err) {
            console.warn(err);
        });
    };



    $scope.uploadAdminProfile = function (idbtn, loading) {
        var btn_book = _(idbtn);
        var loadinggif = _(loading);
        var uploader = new ss.SimpleUpload({
            button: btn_book,
            url: '/criminal/image',
            name: 'upl',
            multipart: true,
            noParams: true,
            hoverClass: 'hover',
            focusClass: 'focus',
            responseType: 'json',
            allowedExtensions: ['jpg', 'jpeg', 'png'],
            onSubmit: function () {
                loadinggif.style.display = 'block';
            },
            onComplete: function (filename, response) {
                if (response.status === "success") {
                    alert('image uploaded successful');
                    // _('admin_image').src = response.data;
                    loadinggif.style.display = 'none';
                } else {
                    loadinggif.style.display = 'none';
                    alert(response.data);
                }
            },
            onError: function () {
                loadinggif.style.display = 'none';
                alert('Unable to upload file');
            }
        });
    };

    $scope.initializeupdatecriminal = function (info) {
        _('addcriminalnew').style.display = "none";
        _('addcriminal').style.display = "block";
        $scope.crime = info;

    };

    $scope.updatestate = function (id) {
        Data.put('/update/criminal/state', {
            dashData: {'id': id}
        }).success(function (response) {
            if (response.status == "success") {
                var priority = 'success';
                var title = 'success';
                var message = response.data;
                $.toaster({priority: priority, title: title, message: message});
                $scope.Listingcriminals();
            } else {
                swal("OOPS...", "you don\'t have access to do this.", "error");
            }
        }).error(function (err) {
            console.log(err);
        });
    };

});

//================================  this is the control of the dashboard page  ==================================//\

app.controller('info_ctrl', function ($scope, Data, $window) {
    
    $scope.import_message = "Please wait...";
    $scope.spreadsheets = [];
    $scope.header_row = ['No', 'First Name', 'Last Name', 'Sex', 'Country', 'Dob', 'Crime date', 'Crime type'];

    $scope.session_dash = function (btn, waitMessage) {
        $scope.Listingcriminals();
        $scope.wantedCriminal();
        $scope.uploadexcel(btn, waitMessage);
    };
    
    $scope.logout = function () {
        Data.delete('/logout')
                .success(function (response) {
                    location.reload();
                }).error(function (err) {
            console.log('connection failed.');
        });
    };
    $scope.Listingcriminals = function () {
        Data.get('/criminal/list')
                .success(function (response) {
                    $scope.criminales = response.data;
                    $scope.readCrime(response.data[0]);

                }).error(function (err) {
            console.warn(err);
        });
    };
    $scope.readCrime = function (info) {
        $scope.crimeinfo = info;
        $scope.historyCriminal($scope.crimeinfo.idnumber);
    };
    $scope.wantedCriminal = function () {
        Data.get('/criminal/wanted')
                .success(function (response) {
                    $scope.wanted = response.data;
                }).error(function (err) {
            console.warn(err);
        });
    };
    $scope.historyCriminal = function (id) {
        Data.get('/criminal/history/' + id)
                .success(function (response) {
                    $scope.history = response.data;
                }).error(function (err) {
            console.warn(err);
        });
    };
    $scope.deleteCrime = function (id) {
        Data.delete('/delete/criminal/' + id)
                .success(function (response) {
                    if (response.status == "success") {
                        swal("YES", response.data, "success");
                        $scope.Listingcriminals();
                    } else {
                        swal("OOPS...", response.data, "error");
                    }
                }).error(function (err) {
            console.log('connection failed.');
        });
    };
    $scope.enableTextarea = function (area, btn) {
        _(area).disabled = false;
        _(btn).disabled = false;
    };
    $scope.updatedescr = function (id, desc) {
        _('showmsg').style.visibility = "visible";
        Data.put('/update/criminal', {
            dashData: {'id': id, 'desc': desc}
        }).success(function (response) {
            if (response.status == "success") {
                _('showmsg').style.visibility = "hidden";
                _('textareadesc').disabled = true;
                _('btndisabled').disabled = true;
            } else {
                _('showmsg').style.visibility = "visible";
                _('showmsg').innerHTML = response.data;
            }
        }).error(function (err) {
            console.log(err);
        });
    };

    $scope.uploadexcel = function (btn, waitMessage) {
        var mybtn = _(btn);
        new ss.SimpleUpload({
            button: mybtn,
            url: '/admin/import/registration',
            name: 'upl',
            multipart: true,
            noParams: true,
            hoverClass: 'hover',
            focusClass: 'focus',
            responseType: 'json',
            allowedExtensions: [],
            onSubmit: function () {
                _(waitMessage).style.visibility = 'visible';
            },
            onComplete: function (filename, response) {
                if (response.status === "success") {
                    var priority = 'success';
                    var title = 'success';
                    var message = 'file uploaded successful';
                    $.toaster({priority: priority, title: title, message: message});
                    $scope.spreadsheets = response.data;
                    $scope.import_message = 'Processing ... it may take a while.';
                    setTimeout(deletemyMessage, 30000);
                } else {
                    var priority = 'error';
                    var title = 'error';
                    var message = response.data;
                    $.toaster({priority: priority, title: title, message: message});
                }
            }
        });
    };

    $scope.save_excel = function (data, context) {
        $scope.import_message = 'Please wait ...';
        Data.post('/admin/import/registration/' + context, {
            dashData: data
        }).success(function (responce) {
            if (responce.status === "success") {
                $scope.import_message = responce.data;
                setTimeout(deletemyMessage, 5000);
            } else {
                $scope.import_message = responce.data;
                setTimeout(deletemyMessage, 5000);
            }
        }).error(function (err) {
            console.log('connection failed.');
        });
    };

    $scope.choosecontext = function (context) {
        _('twobtngroup').style.visibility = 'visible';
        $scope.context = context;
    };

});

//================================  this is the control of the dashboard page  ==================================//\

app.controller('set_ctrl', function ($scope, Data, $window) {

    $scope.session_dash = function () {
        $scope.Listinguser();
    };
    $scope.logout = function () {
        Data.delete('/logout')
                .success(function (response) {
                    location.reload();
                }).error(function (err) {
            console.log('connection failed.');
        });
    };
    $scope.Listinguser = function () {
        Data.get('/user/list')
                .success(function (response) {
                    $scope.users = response.data;
                }).error(function (err) {
            console.warn(err);
        });
    };

    $scope.adduser = function (sign) {
        var pass1 = _('pass1').value;
        var pass2 = _('pass2').value;
        if (pass1 !== pass2) {
            _('isa_error').style.display = "block";
            _('isa_error_message').innerHTML = 'password don\'t match';
            setTimeout(hidden_error, 5000);
            return false;
        }
        Data.post('/add/user', {
            dashData: sign
        }).success(function (response) {
            if (response.status == "success") {
                _('isa_success').style.display = "block";
                _('isa_error').style.display = "none";
                _('isa_success_message').innerHTML = response.data;
                $scope.Listinguser();
                _('adduser').reset();
                setTimeout(hidden_error, 5000);
            } else {
                _('isa_success').style.display = "none";
                _('isa_error').style.display = "block";
                _('isa_error_message').innerHTML = response.data;
                setTimeout(hidden_error, 5000);
            }
        }).error(function (err) {
            console.log(err);
        });
    };

    $scope.edituser = function (id, prive) {
        Data.get('/editting/user/' + id + '/' + prive)
                .success(function (response) {
                    if (response.status === "success") {
                        swal("YES", response.data, "success");
                        $scope.Listinguser();
                    } else {
                        swal("OOPS...", response.data, "error");
                    }
                }).error(function (err) {
            console.log(err);
        });
    };

});

//================================  this is the control of the dashboard page  ==================================//\

app.controller('pub_ctrl', function ($scope, Data, $window) {

    $scope.session_dash = function (idbtn, loading) {
        $scope.wantedCriminal();
        $scope.uploadAdminProfile(idbtn, loading);
    };
    $scope.logout = function () {
        Data.delete('/logout')
                .success(function (response) {
                    location.reload();
                }).error(function (err) {
            console.log('connection failed.');
        });
    };
    $scope.wantedCriminal = function () {
        Data.get('/criminal/wanted')
                .success(function (response) {
                    $scope.wanted = response.data;
                }).error(function (err) {
            console.warn(err);
        });
    };

    $scope.publishcrime = function (sign) {
        Data.post('/add/criminal/wanted', {
            dashData: sign
        }).success(function (response) {
            if (response.status == "success") {
                _('isa_success').style.display = "block";
                _('isa_error').style.display = "none";
                _('isa_success_message').innerHTML = response.data;
                $scope.Listingcriminals();
                setTimeout(hidden_error, 5000);
            } else {
                _('isa_success').style.display = "none";
                _('isa_error').style.display = "block";
                _('isa_error_message').innerHTML = response.data;
            }
        }).error(function (err) {
            console.log(err);
        });
    };

    $scope.uploadAdminProfile = function (idbtn, loading) {
        var btn_book = _(idbtn);
        var loadinggif = _(loading);
        var uploader = new ss.SimpleUpload({
            button: btn_book,
            url: '/criminal/image',
            name: 'upl',
            multipart: true,
            noParams: true,
            hoverClass: 'hover',
            focusClass: 'focus',
            responseType: 'json',
            allowedExtensions: ['jpg', 'jpeg', 'png'],
            onSubmit: function () {
                loadinggif.style.display = 'block';
            },
            onComplete: function (filename, response) {
                if (response.status === "success") {
                    alert('image uploaded successful');
                    // _('admin_image').src = response.data;
                    loadinggif.style.display = 'none';
                } else {
                    loadinggif.style.display = 'none';
                    alert(response.data);
                }
            },
            onError: function () {
                loadinggif.style.display = 'none';
                alert('Unable to upload file');
            }
        });
    };

});

//================================ function that help me to minimizing my codes ====================================

function _(x) {
    return document.getElementById(x);
}
function deletemyMessage() {
    _('loadingxslfile').style.visibility = "hidden";
}

function hidden_error_sign_in() {
    _('isa_error').style.display = 'none';
    _('isa_success').style.display = 'none';
}
function hidden_error() {
    _('isa_errorm').style.display = 'none';
    _('isa_successm').style.display = 'none';
    _('isa_errorf').style.display = 'none';
    _('isa_successf').style.display = 'none';
    _('isa_errorc').style.display = 'none';
    _('isa_successc').style.display = 'none';
    _('isa_errori').style.display = 'none';
    _('isa_successi').style.display = 'none';
    $('#adduserpop').trigger('click');
}

function hidden_error_sales() {
    _('isa_error_sales').style.display = 'none';
}
function hidden_error_calcualtionPrix() {
    _('isa_error_unit').style.display = 'none';
    _('isa_success_interet').style.display = 'none';
    _('isa_error_interet').style.display = "none";
}