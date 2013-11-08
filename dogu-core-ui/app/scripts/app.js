'use strict';

angular.module('doguCoreUiApp', [
  'ngCookies',
  'ngResource',
  'ngSanitize',
  'ngRoute',
  'ngAnimate',
  'angularBootstrapNavTree'
])

  // Active link for Navigation
  .directive('activeLink', function($location) {
    return {
      restrict: 'A',
      link: function(scope, element, attrs) {
        var clazz = attrs.activeLink;
        var path = element[0].firstChild.getAttribute('ng-href').replace(/^!?#/, '');
        scope.location = $location;
        scope.$watch('location.path()', function(newPath) {
          if (path === newPath) {
            element.addClass(clazz);
          } else {
            element.removeClass(clazz);
          }
        });
      }
    };
  })

  // Routes
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/home.html',
        controller: 'HomeCtrl'
      })
      .when('/vcloud-inspector', {
        templateUrl: 'views/vcloud-inspector.html',
        controller: 'VCloudInspectorCtrl'
      })
      .otherwise({
        redirectTo: '/'
      });
  })

  // HTML5 mode for Location Provider
  .config(function ($locationProvider) {
    $locationProvider.html5Mode(true).hashPrefix('!');
  });
