<?php namespace JobLion\AuthBundle\Controller;

use JobLion\AppBundle\Controller\AbstractController;

use JobLion\AppBundle\Entity;
use JobLion\AuthBundle\Password;
use JobLion\AuthBundle\Token;
use JobLion\AuthBundle\ConfirmationMail;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Auth api controller
 */
class AuthController extends AbstractController
{
    /**
     * @api        {post} /v1/auth/register register
     * @apiName    registerUser
     * @apiVersion 0.1.0
     * @apiGroup   Auth
     *
     * @apiParam {String} email               User email
     * @apiParam {String} firstName           User first name
     * @apiParam {String} lastName            User last name
     * @apiParam {String} password            User password
     *
     * @apiSuccess {Number} id                Id of the newly created User
     *
     * @apiError        MissingValues         Some values weren't transmited
     * @apiError        InvalidEmail          E-Mail is invalid
     * @apiError        EmailExists           E-Mail is already in use
     * @apiError        InvalidPassword       Password is invalid
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Creates a new User
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function register(Request $request)
    {
        $email = $request->get('email');
        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $password = $request->get('password');

        // check for missing values
        if (!$email || !$firstName || !$lastName || !$password) {
            return $this->app->json(
              ["error" => "MissingValues"],
              400
            );
        }

        // check if email already exists
        $testUser = $this->entityManager
                            ->getRepository(Entity\User::class)
                            ->findOneBy(array('email' => $email));
        if ($testUser) {
            return $this->app->json(
              ["error" => "EmailExists"],
              409
            );
        }

        // check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(
              ["error" => "InvalidEmail"],
              400
            );
        }

        // check if password is secure
        if (strlen($password) < 6) {
            return $this->app->json(
              ["error" => "InvalidPassword"],
              400
            );
        }

        // create user
        $user = new Entity\User();
        $user->setEmail($email)
             ->setFirstName($firstName)
             ->setLastName($lastName)
             ->setHash(Password::hash($password));


        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // generate and send confirmation code
        ConfirmationMail::accountActivation($user, $this->app['isTest']);
        $this->entityManager->flush();

        // return success TODO: update to correct api url (if changed)
        $response = new JsonResponse(["id" => $user->getId()], 201);
        $url = $this->generateUrl(
          "user",
          $user->getId()
        );
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * @api        {post} /v1/auth/login login
     * @apiName    loginUser
     * @apiVersion 0.1.0
     * @apiGroup   Auth
     *
     * @apiParam {String} email               User email
     * @apiParam {String} password            User password
     *
     * @apiSuccess {String} token             Access token
     * @apiSuccess {Object} user              The user object for this user
     * @apiSuccess {Number} expire            timestamp for when the token expires
     *
     * @apiError        MissingValues         Some values weren't transmited
     * @apiError        InvalidLogin          E-Mail or Password wrong
     * @apiError        NotActivated          Confirmation E-Mail has not been clicked on
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Login a user
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function login(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');

        // check for missing values
        if (!$email || !$password) {
            return $this->app->json(
              ["error" => "MissingValues"],
              400
            );
        }

        // check if email and password are correct
        $user = $this->entityManager
                        ->getRepository(Entity\User::class)
                        ->findOneBy(array('email' => $email));

        if (!$user || !Password::check($user, $password)) {
            return $this->app->json(
              ["error" => "InvalidLogin"],
              401
            );
        }

        // check if account has been activated
        if (!$user->getActivated()) {
            return $this->app->json(
              ["error" => "NotActivated"],
              401
            );
        }

        // return login token
        $token = new Token($this->configFile, $this->entityManager);
        $jwtToken = $token->generate($user);

        return $this->app->json(
          [
            "token" => $jwtToken['token'],
            "expire" => $jwtToken['data']['exp'],
            "user" => $user->toArray()
          ],
          200
        );
    }

    /**
     * @api        {get} /v1/auth/info login info
     * @apiName    infoUser
     * @apiVersion 0.1.0
     * @apiGroup   Auth
     *
     * @apiSuccess {Array} user               User info about you
     * @apiUse Login
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "user" : {
     *               "id": Number,
     *               "avatar": String,
     *               "firstName" : String,
     *               "lastName" : String
     *         }
     *     }
     **/

    /**
     * Get info about the current user
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
    */
    public function info(Request $request)
    {
        // check if logged in
        $error = $this->requireLogin($request);
        if ($error) {
            return $error;
        }


        return $this->app->json(
          ["user" => $this->user->toArray()],
          200
        );
    }

    /**
     * @api        {get} /v1/auth/activate activate
     * @apiName    activateUser
     * @apiVersion 0.1.0
     * @apiGroup   Auth
     *
     * @apiParam {Number} user                User id
     * @apiParam {String} code                Confirmation code
     *
     * @apiError        MissingValues         Some values weren't transmited
     * @apiError        Invalid               Id or code wrong
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Activate a user
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function activate(Request $request)
    {
        $userId = $request->get('user');
        $code = $request->get('code');

        // check for missing values
        if (!$userId || !$code) {
            return $this->app->json(
              ["error" => "MissingValues"],
              400
            );
        }

        // check if user id and code are correct
        $user = $this->entityManager->find(Entity\User::class, $userId);

        if (!$user || $user->getActivationCode() !== $code) {
            return $this->app->json(
              ["error" => "Invalid"],
              400
            );
        }

        // activate the user
        $user->setActivated(true);
        $user->setActivationCode(null);
        $this->entityManager->flush();

        return $this->app->json(
          ["success" => true],
          200
        );
    }
}
