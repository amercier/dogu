'use strict';

angular.module('doguCoreUiApp').controller('VCloudInspectorCtrl', function ($scope, $http/*, $location*/) {

  $scope.listLoading = true;
  $scope.listError = false;

  var allTypes = {};
  var allObjects = {};
  var objectsById = {};

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

  $scope.updateObjects = function() {
    $scope.objects = this.selectedType && allObjects[ this.selectedHost ][ this.selectedType.id ];
  };
  $scope.onSelectedHostChange = $scope.updateObjects;
  $scope.onSelectedTypeChange = $scope.updateObjects;

  $scope.objectFilter = '';
  var keywords = [];
  $scope.onFilterChange = function() {
    keywords = this.objectFilter.split(/ +/g);
  };

  $scope.filterObject = function(object) {
    return keywords.every(function (keyword) {
      return (object.host = $scope.selectedHost) &&
        object.name.toLowerCase().indexOf(keyword.toLowerCase()) !== -1;
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

        // Check for URL values, and set "reference" to the object
        var matches;
        jQuery.each(data.data.values, function(property, valueObject) {
          jQuery.each(valueObject.history, function(historyRank, historyObject) {
            if (typeof historyObject.value === 'string' && (matches = historyObject.value.match(/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/))) {
              historyObject.reference = objectsById[ matches[0] ];
            }
          });
        });

        $scope.object = data.data;
      })
      .error(function(data/*, status, headers, config*/) {
        $scope.objectLoading = false;
        $scope.objectError = data;
      });
  };

  $scope.onReferenceClick = function(reference) {
    $scope.selectedHost = reference.host;
    $scope.selectedType = allTypes[reference.queryType];
    $scope.selectedObject = reference;
    $scope.updateObjects();
    $scope.onObjectSelected(reference);
  };

  $scope.getDate = function(time) {
    var d = new Date(1000 * +time);
    return d.getFullYear() + '-' + ('0'+d.getMonth()).slice(-2) + '-' + ('0'+d.getDate()).slice(-2) +
      ' ' + ('0'+d.getHours()).slice(-2) + ':' + ('0'+d.getMinutes()).slice(-2);
  };

  $scope.isLink = function(history) {
    return history.reference;
  };

  $scope.getLink = function(object) {
    return '/vcloud-inspector/' + object.host + '/' + object.queryType + '/' + object.object;
  };

  $scope.getTypeName = function(type) {
    return allTypes[type].name;
  };

  // Get all objects and update objects
  $http({method: 'GET', url: 'http://dogu.local/vcloud-inspector'})
    .success(function(data/*, status, headers, config*/) {
      $scope.listLoading = false;

      // Create a allObjects which is an object of the form
      //     {
      //       <host>: {
      //         <queryType>: {
      //           <id>: object,
      //           ...
      //         ],
      //         ...
      //       },
      //       ...
      //     }
      jQuery.each(data.data.objects, function (objectId, object) {

        // Save object in objectsById
        objectsById[ object.object ] = object;

        // If necessary, create allObject.<host> = {}
        if (!(object.host in allObjects)) {
          allObjects[object.host] = {};
        }

        // If necessary, create allObjects.<host>.<queryType> = []
        if (!(object.queryType in allObjects[object.host])) {
          allObjects[object.host][object.queryType] = [];
        }

        // Save object
        object.id = objectId;
        allObjects[object.host][object.queryType].push(object);
      });

      // Sort object arrays of allObjects
      for (var host in allObjects) {
        if (allObjects.hasOwnProperty(host)) {
          for(var queryType in allObjects[host]) {
            if (allObjects[host].hasOwnProperty(queryType)) {
              allObjects[host][queryType].sort(getObjectComparator('name'));
            }
          }
        }
      }

      // Update $scope.hosts
      $scope.hosts = data.data.hosts.sort();

      // Update $scope.types
      $scope.types = jQuery.map(data.data.types, function(name, id) {
        allTypes[id] = {
          id: id,
          name: name
        };
        return allTypes[id];
      }).sort(getObjectComparator('name'));

      // Initialize default values
      if ($scope.hosts.length) {
        $scope.selectedHost = $scope.hosts[0];
      }
      if ($scope.types.length) {
        $scope.selectedType = $scope.types[0];
      }
      $scope.updateObjects();

    })
    .error(function(data/*, status, headers, config*/) {
      $scope.listLoading = false;
      $scope.listError = data;
    });

});
