'use strict';

angular.module('doguCoreUiApp')
  .controller('HomeCtrl', function ($scope) {

    $scope.modules = [
      {
        id: 'vcloud-inspector',
        name: 'vCloud Inspector'
      }
    ];

    $scope.getHref = function(module) {
      return '/' + module.id;
    };

  });
