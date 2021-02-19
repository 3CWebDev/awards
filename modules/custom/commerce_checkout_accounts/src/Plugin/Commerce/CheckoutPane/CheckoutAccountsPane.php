<?php

namespace Drupal\commerce_checkout_accounts\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

use Drupal\commerce\CredentialsCheckFloodInterface;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the login pane.
 *
 * @CommerceCheckoutPane(
 *   id = "CheckoutAccountsPane",
 *   label = @Translation("Login, Create Account, or continue as guest"),
 * )
 */
class CheckoutAccountsPane extends CheckoutPaneBase implements CheckoutPaneInterface, ContainerFactoryPluginInterface {

  /**
   * The credentials check flood controller.
   *
   * @var \Drupal\commerce\CredentialsCheckFloodInterface
   */
  protected $credentialsCheckFlood;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The user authentication object.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * The client IP address.
   *
   * @var string
   */
  protected $clientIp;

  /**
   * Constructs a new Login object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce\CredentialsCheckFloodInterface $credentials_check_flood
   *   The credentials check flood controller.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, CredentialsCheckFloodInterface $credentials_check_flood, AccountInterface $current_user, UserAuthInterface $user_auth, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);

    $this->credentialsCheckFlood = $credentials_check_flood;
    $this->currentUser = $current_user;
    $this->userAuth = $user_auth;
    $this->clientIp = $request_stack->getCurrentRequest()->getClientIp();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $checkout_flow,
      $container->get('entity_type.manager'),
      $container->get('commerce.credentials_check_flood'),
      $container->get('current_user'),
      $container->get('user.auth'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'allow_guest_checkout' => TRUE,
        'allow_registration' => FALSE,
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    if (!empty($this->configuration['allow_guest_checkout'])) {
      $summary = $this->t('Guest checkout: Allowed');
    }
    else {
      $summary = $this->t('Guest checkout: Not allowed') . '<br>';
      if (!empty($this->configuration['allow_registration'])) {
        $summary .= $this->t('Registration: Allowed');
      }
      else {
        $summary .= $this->t('Registration: Not allowed');
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['allow_guest_checkout'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow guest checkout'),
      '#default_value' => $this->configuration['allow_guest_checkout'],
    ];
    $form['allow_registration'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow registration'),
      '#default_value' => $this->configuration['allow_registration'],
      '#states' => [
        'visible' => [
          ':input[name="configuration[panes][login][configuration][allow_guest_checkout]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['allow_guest_checkout'] = !empty($values['allow_guest_checkout']);
      $this->configuration['allow_registration'] = !empty($values['allow_registration']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return $this->currentUser->isAnonymous();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['#attached']['library'][] = 'commerce_checkout/login_pane';
    $pane_form['#attached']['library'][] = 'commerce_checkout_accounts/commerce-checkout-accounts';

    $pane_form['returning_customer'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Returning Customer'),
      '#attributes' => [
        'class' => [
          'form-wrapper__login-option',
          'form-wrapper__returning-customer',
        ],
      ],
    ];
    $pane_form['returning_customer']['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => FALSE,
    ];

    $pane_form['returning_customer']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#size' => 60,
    ];
    $pane_form['returning_customer']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Log in'),
      '#op' => 'login',
    ];
    $pane_form['returning_customer']['forgot_password'] = [
      '#type' => 'markup',
      '#markup' => Link::createFromRoute($this->t('Forgot password?'), 'user.pass')->toString(),
    ];

    $pane_form['register'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('New Customer'),
      '#access' => $this->configuration['allow_registration'],
      '#attributes' => [
        'class' => [
          'form-wrapper__login-option',
          'form-wrapper__guest-checkout',
        ],
      ],
    ];
    $pane_form['register']['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => FALSE,
    ];
    $pane_form['register']['password'] = [
      '#type' => 'password_confirm',
      '#size' => 60,
      '#description' => $this->t('Provide a password for the new account in both fields.'),
      '#required' => FALSE,
    ];


    //Get the EntityFormDisplay (i.e. the default Form Display) of this content type
    //https://www.drupal.org/forum/support/module-development-and-code-questions/2015-09-05/drupal-8-equivalent-to-field_attach
    $user = \Drupal::service('entity_type.manager')->getStorage('user')->create(array('type' => 'user'));
    $entity_form_display = \Drupal::service('entity_type.manager')->getStorage('entity_form_display')->load('user.user.register');

    $widget = $entity_form_display->getRenderer('field_tax_exempt');
    $items = $user->get('field_tax_exempt');
    $items->filterEmptyItems();

    $pane_form['register']['tax_exempt'] = $widget->form($items, $pane_form, $form_state);

    $widget = $entity_form_display->getRenderer('field_tax_document');
    $items = $user->get('field_tax_document');
    $items->filterEmptyItems();
    $pane_form['register']['tax_document'] = $widget->form($items, $pane_form, $form_state);
    $pane_form['register']['tax_document']['#states'] = array(
        'visible' => array(
            ':input[name="CheckoutAccountsPane[field_tax_exempt][value]"]' => array('checked' => TRUE),
        ),
    );
    $pane_form['register']['register'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create account and continue'),
      '#op' => 'register',
    ];


    $pane_form['guest'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Guest Checkout'),
      '#access' => $this->configuration['allow_guest_checkout'],
      '#attributes' => [
        'class' => [
          'form-wrapper__login-option',
          'form-wrapper__guest-checkout',
        ],
      ],
    ];
    $pane_form['guest']['text'] = [
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#markup' => $this->t('Proceed to checkout. You can optionally create an account at the end.'),
    ];
    $pane_form['guest']['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue as Guest'),
      '#op' => 'continue',
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $triggering_element = $form_state->getTriggeringElement();

    // Try to determine of user is submitting the New Customer form
    if ($triggering_element['#op'] == 'login'){
      if (!empty($values['register']['mail']) || !empty(trim($values['register']['password']))){
        $triggering_element['#op'] = 'register';
      }
    }
    switch ($triggering_element['#op']) {
      case 'continue':
        return;

      case 'login':

        //$mail_element = $pane_form['returning_customer']['mail'];
        $mail_element = 'returning_customer][mail';
        //$password_element = $pane_form['returning_customer']['password'];
        //$username = $values['returning_customer']['name'];
        $mail = $values['returning_customer']['mail'];
        $password = trim($values['returning_customer']['password']);
        // Generate the "reset password" url.
        $query = !empty($mail) ? ['mail' => $mail] : [];
        $password_url = Url::fromRoute('user.pass', [], ['query' => $query])->toString();

        if (empty($mail) || empty($password)) {
          $form_state->setError($pane_form['returning_customer'], $this->t('Unrecognized email address or password. <a href=":url">Have you forgotten your password?</a>', [':url' => $password_url]));
          return;
        }

        /* Custom Code to lookup username from email address*/
        if ($username = \Drupal::database()->query("SELECT name FROM {users_field_data} WHERE LOWER(mail) = LOWER(:mail)", array(
          ':mail' => $mail,
        ))->fetchField()) {
          $form_state->setValue('name', array($username));
        }else{
          $form_state->setValue('name', array('-'));
        }

        if (empty($username)) {
          $form_state->setError($pane_form['returning_customer'], $this->t('Unrecognized email address or password. <a href=":url">Have you forgotten your password?</a>', [':url' => $password_url]));
          return;
        }
        /* End Custom Code */

        if (user_is_blocked($username)) {
          $form_state->setErrorByName($mail_element, $this->t('The username %name has not been activated or is blocked.', ['%name' => $username]));
          return;
        }
        if (!$this->credentialsCheckFlood->isAllowedHost($this->clientIp)) {
          $form_state->setErrorByName($mail_element, $this->t('Too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => Url::fromRoute('user.pass')]));
          $this->credentialsCheckFlood->register($this->clientIp, $username);
          return;
        }
        elseif (!$this->credentialsCheckFlood->isAllowedAccount($this->clientIp, $username)) {
          $form_state->setErrorByName($mail_element, $this->t('Too many failed login attempts for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => Url::fromRoute('user.pass')]));
          $this->credentialsCheckFlood->register($this->clientIp, $username);
          return;
        }

        $uid = $this->userAuth->authenticate($username, $password);
        if (!$uid) {
          $this->credentialsCheckFlood->register($this->clientIp, $username);
          $form_state->setErrorByName($mail_element, $this->t('Unrecognized username or password. <a href=":url">Have you forgotten your password?</a>', [':url' => $password_url]));
        }
        $form_state->set('logged_in_uid', $uid);
        break;

      case 'register':
        $email = $values['register']['mail'];
        $password = trim($values['register']['password']);

        if (empty($email)) {
          $form_state->setError($pane_form['register']['mail'], $this->t('Email field is required.'));
          return;
        }

        if (empty($password)) {
          $form_state->setError($pane_form['register']['password'], $this->t('Password field is required.'));
          return;
        }

        /** @var \Drupal\user\UserInterface $account */
        $account = $this->entityTypeManager->getStorage('user')->create([
          'mail' => $email,
          'name' => $email,
          'pass' => $password,
          'status' => TRUE,
        ]);

        $tax_exempt = $values['field_tax_exempt']['value'];
        $account->set('field_tax_exempt' , $tax_exempt);
        if ($tax_exempt === 1){
          $fid = $values['field_tax_document'][0]['fids'][0];
          if (!is_numeric($fid)){
            $form_state->setError($pane_form['register']['tax_document'], $this->t('Tax document is required for tax exempt status.'));
            return;
          }
          $account->set('field_tax_document' , ['target_id' => $fid]);
        }
        // Validate the entity. This will ensure that the username and email
        // are in the right format and not already taken.
        $violations = $account->validate();
        foreach ($violations->getByFields(['mail']) as $violation) {
          list($field_name) = explode('.', $violation->getPropertyPath(), 2);
          //$form_state->setError($pane_form['register'][$field_name], $this->t($violation->getMessage()));
          //\Drupal::logger('my_module')->error('<pre>' . print_r($violation->getMessage(),1) . '</pre>');
          $form_state->setError($pane_form['register']['mail'], $violation->getMessage());
          return;
        }

        if (!$form_state->hasAnyErrors()) {
          $account->save();
          $form_state->set('logged_in_uid', $account->id());
        }
        _user_mail_notify('register_no_approval_required', $account, $langcode = NULL);
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $triggering_element = $form_state->getTriggeringElement();
    //\Drupal::logger('my_module')->notice($triggering_element['#op']);

    switch ($triggering_element['#op']) {
      case 'continue':
        break;

      case 'login':
      case 'register':
        $storage = $this->entityTypeManager->getStorage('user');
        /** @var \Drupal\user\UserInterface $account */
        $account = $storage->load($form_state->get('logged_in_uid'));
        user_login_finalize($account);
        $this->order->setCustomer($account);
        $this->credentialsCheckFlood->clearAccount($this->clientIp, $account->getAccountName());
        break;
    }

    $form_state->setRedirect('commerce_checkout.form', [
      'commerce_order' => $this->order->id(),
      'step' => $this->checkoutFlow->getNextStepId($this->getStepId()),
    ]);
  }
}