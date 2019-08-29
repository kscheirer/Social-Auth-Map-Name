<?php

namespace Drupal\social_auth_map_name\EventSubscriber;

use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_api\Plugin\NetworkManager;

/**
 * Class SocialAuthSubscriber.
 */
class SocialAuthSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\social_auth\SocialAuthDataHandler definition.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $socialAuthDataHandler;

  /**
   * Drupal\social_api\Plugin\NetworkManager definition.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  protected $pluginNetworkManager;

  /**
   * The provider auth manager.
   *
   * @var \Drupal\social_auth\AuthManager\OAuth2ManagerInterface
   */
  private $providerAuth;

  /**
   * Constructs a new SocialAuthSubscriber object.
   */
  public function __construct(SocialAuthDataHandler $social_auth_data_handler, NetworkManager $plugin_network_manager, OAuth2ManagerInterface $providerAuth) {
    $this->socialAuthDataHandler = $social_auth_data_handler;
    $this->pluginNetworkManager = $plugin_network_manager;
    $this->providerAuth = $providerAuth;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_CREATED] = ['onUserCreated'];

    return $events;
  }

  /**
   * This method is called when the SocialAuthEvents::USER_CREATED is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   *
   * @throws
   */
  public function onUserCreated(UserEvent $event) {
    // Sets prefix.
    $this->socialAuthDataHandler->setSessionPrefix($event->getPluginId());

    // Gets client object.
    $client = $this->pluginNetworkManager->createInstance($event->getPluginId())->getSdk();

    // Create provider OAuth2 manager.
    // Can also use $client directly and request data using the library/SDK.
    $this->providerAuth->setClient($client)
      ->setAccessToken($this->socialAuthDataHandler->get('access_token'));



    // Gets user info.
    $userInfo = $this->providerAuth->getUserInfo();

    /*
     * @var Drupal\user\UserInterface $user
     *
     * For all available methods, see User class
     * @see https://api.drupal.org/api/drupal/core!modules!user!src!Entity!User.php/class/User
     */
    $user = $event->getUser();
    $user->set('first_name', $userInfo->getFirstName());
    $user->set('last_name', $userInfo->getLastName());
  }
}
