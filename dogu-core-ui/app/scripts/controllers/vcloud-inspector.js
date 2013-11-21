'use strict';

angular.module('doguCoreUiApp').controller('VCloudInspectorCtrl', function ($scope, $http) {

  $scope.listLoading = true;
  $scope.listError = false;

  $scope.objectTree = false;

  var objectsByQueryType = {};

  function getObjectComparator(fieldName, caseSensitive) {
    return function(type1, type2) {
      var value1 = caseSensitive ? type1[fieldName] : type1[fieldName].toLowerCase(),
          value2 = caseSensitive ? type2[fieldName] : type2[fieldName].toLowerCase();
      if (value1 < value2) {
        return -1;
      }
      else if (value1 > value2) {
        return 1;
      }
      else {
        return 0;
      }
    };
  }

  $scope.onSelectedTypeChange = function() {
    $scope.objects = objectsByQueryType[ this.selectedType.id ];
  };

  $scope.objectFilter = '';
  var keywords = [];
  $scope.onFilterChange = function() {
    keywords = this.objectFilter.split(/ +/g);
  };

  $scope.filterObject = function(object) {
    return keywords.every(function (keyword) {
      return object.name.toLowerCase().indexOf(keyword.toLowerCase()) !== -1;
    });
  };

  $scope.selectedObject = null;
  $scope.isObjectSelected = function(object) {
    return $scope.selectedObject && object.id === $scope.selectedObject.id;
  };

  $scope.onObjectSelected = function(object) {
    $scope.object = object;
    $scope.selectedObject = object;
    $scope.objectLoading = true;
    $scope.objectError = false;
    $http({method: 'GET', url: 'http://dogu.local/vcloud-inspector/' + object.id})
      .success(function(data/*, status, headers, config*/) {
        $scope.objectLoading = false;
        $scope.object = data.data;
      })
      .error(function(data/*, status, headers, config*/) {
        $scope.objectLoading = false;
        $scope.objectError = data;
      });
  };

  $scope.getDate = function(time) {
    var d = new Date(1000 * +time);
    return d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate() +
      ' ' + ('0'+d.getHours()).slice(-2) + ':' + ('0'+d.getMinutes()).slice(-2);
  };

  // Get all objects and update objects
  $http({method: 'GET', url: 'http://dogu.local/vcloud-inspector'})
    .success(function(data/*, status, headers, config*/) {
      $scope.listLoading = false;

      // Create a objectTreeObject which is an object of the form
      // queryType => [object1, object2, ...]
      jQuery.each(data.data.objects, function (objectId, object) {
        if (!(object.queryType in objectsByQueryType)) {
          objectsByQueryType[object.queryType] = [];
        }
        object.id = objectId;
        objectsByQueryType[object.queryType].push(object);
      });

      // Sort object arrays of objectTreeObject
      for (var i in objectsByQueryType) {
        if (objectsByQueryType.hasOwnProperty(i)) {
          objectsByQueryType[i] = objectsByQueryType[i].sort(getObjectComparator('name'));
        }
      }

      // Update $scope.types
      $scope.types = jQuery.map(data.data.types, function(name, id) {
        return {
          id: id,
          name: name
        };
      }).sort(getObjectComparator('name'));

    })
    .error(function(data/*, status, headers, config*/) {
      $scope.listLoading = false;
      $scope.listError = data;
    });

});
