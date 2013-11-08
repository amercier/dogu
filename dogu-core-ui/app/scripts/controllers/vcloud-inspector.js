'use strict';

angular.module('doguCoreUiApp')

  .controller('VCloudInspectorCtrl', function ($scope, $http) {

    $scope.loading = true;
    $scope.listError = false;

    $scope.objectTree = false;

    $http({method: 'GET', url: 'http://dogu.local/vcloud-inspector'})
      .success(function(data/*, status, headers, config*/) {
        $scope.loading = false;

        // Create a objectTreeObject which is an object of the form
        // queryType => [object1, object2, ...]
        var objectsByQueryType = {};
        jQuery.each(data.data.objects, function (objectId, object) {
          if (!(object.queryType in objectsByQueryType)) {
            objectsByQueryType[object.queryType] = [];
          }
          object.id = objectId;
          objectsByQueryType[object.queryType].push(object);
        });

        // Update $scope.objectTree from this object
        var getObjectLeaf = function (object) {
          return {
            uid: object.id,
            type: 'object',
            label: object.name,
            children: []
          };
        };
        $scope.objectTree = [];
        jQuery.each(objectsByQueryType, function (queryType, objects) {
          $scope.objectTree.push({
            uid: queryType,
            type: 'queryType',
            label: data.data.types[queryType],
            children: objects.map(getObjectLeaf)
          });
        });

        console.log('objectsByQueryType', objectsByQueryType);
        console.log('$scope.objectTree', $scope.objectTree);
      })
      .error(function(data/*, status, headers, config*/) {
        $scope.loading = false;
        $scope.listError = data;
      });

    // $scope.loading = false;
    // $scope.objectTree = [
    //   {
    //     label: 'vApps',
    //     children: ['vApp 1', 'vApp 2', 'vApp 3']
    //   }
    // ];

    $scope.onObjectSelected = function(branch) {
      console.log(branch);
    };

  });
