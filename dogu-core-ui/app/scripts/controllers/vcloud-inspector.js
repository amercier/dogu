'use strict';

angular.module('doguCoreUiApp')

  .controller('VCloudInspectorCtrl', function ($scope, $http) {

    $scope.loading = true;
    $scope.listError = false;

    $http({method: 'GET', url: 'http://dogu.local/vcloud-inspector'})
      .success(function(data, status, headers, config) {
        $scope.loading = false;
        $scope.apply();
      })
      .error(function(data, status, headers, config) {
        $scope.loading = false;
        $scope.listError = data;
        $scope.apply();
      });

  });
