angular.module('So_Pharma.services', [])

.factory("Data", ['$http',
    function ($http) { 
        var obj = {};

        obj.get = function (q) {
            return $http.get(q).success(function (data) {
                return data;
            });
        };
        obj.post = function (q, object) {
            return $http({
                    url: q,
                    data: object.dashData,
                    method: "POST",
                    headers: {'Content-Type':'application/json; charset=UTF-8'}
            }).success(function(data) {
                    return data;
            }).error(function(err) {
                    return err;
            });
        };
        obj.delete = function (q) {
            return $http.delete(q).success(function (data) {
                return data;
            });
        };
        obj.put = function (q, object) {
            return $http({
                    url: q,
                    data: object.dashData,
                    method: "PUT",
                    headers: {'Content-Type':'application/json; charset=UTF-8'}
            }).success(function(data) {
                    return data;
            }).error(function(err) {
                    return err;
            });
        };

      return obj;
}]);
