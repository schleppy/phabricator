<?php

final class PhabricatorNotificationsApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Notifications');
  }

  public function getBaseURI() {
    return '/notification/';
  }

  public function getShortDescription() {
    return pht('Real-Time Updates and Alerts');
  }

  public function getRoutes() {
    return array(
      '/notification/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?'
          => 'PhabricatorNotificationListController',
        'panel/' => 'PhabricatorNotificationPanelController',
        'individual/' => 'PhabricatorNotificationIndividualController',
        'status/' => 'PhabricatorNotificationStatusController',
        'clear/' => 'PhabricatorNotificationClearController',
        'test/' => 'PhabricatorNotificationTestController',
      ),
    );
  }

  public function isLaunchable() {
    return false;
  }

}
