<?php namespace JobLion\Api\Controller;

use JobLion\Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * User api controller
 */
class User extends AbstractController
{
    /**
     * Creates a new User
     *
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     *
     * @api        {post} /user/create create
     * @apiName    createUser
     * @apiVersion 0.1.0
     * @apiGroup   User
     *
     * @apiParam {String} email               User email
     * @apiParam {String} firstName           User first name
     * @apiParam {String} lastName            User last name
     * @apiParam {String} password            User password
     *
     * @apiSuccess {bool} success             Status of the user creation
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
    public function create(Request $request)
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

        // create user
        $user = new Database\Account\User($this->db);
        try {
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPassword($password);
            $user->create();

            // return success
            return $this->app->json(
              ["success" => true],
              200
            );
        }
        // catch errors
        catch (Database\Exception\EmailExists $e) {
            return $this->app->json(
              ["error" => "EmailExists", "message" => $e->getMessage()],
              409
            );
        } catch (Database\Exception\InvalidEmail $e) {
            return $this->app->json(
              ["error" => "InvalidEmail", "message" => $e->getMessage()],
              400
            );
        } catch (Database\Exception\InvalidPassword $e) {
            return $this->app->json(
              ["error" => "InvalidPassword", "message" => $e->getMessage()],
              400
            );
        }
    }

    /**
     * Login a user
     *
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     *
     * @api        {post} /user/login login
     * @apiName    loginUser
     * @apiVersion 0.1.0
     * @apiGroup   User
     *
     * @apiParam {String} email               User email
     * @apiParam {String} password            User password
     *
     * @apiSuccess {bool} success             Status of the login
     *
     * @apiError        MissingValues         Some values weren't transmited
     * @apiError        InvalidLogin          E-Mail or Password wrong
     * @apiError        AlreadyLoggedIn       You are already loggedin
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/
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

        // check if user is already logged in
        if ($this->getLogin()) {
            return $this->app->json(
              ["error" => "AlreadyLoggedIn"],
              409
            );
        }

        // check if email and password are correct
        $user = new Database\Account\User($this->db);
        if (!$user->loadEmail($email) || !$user->checkPassword($password)) {
            return $this->app->json(
              ["error" => "InvalidLogin"],
              401
            );
        }

        // login user
        $this->loginUser($user);

        return $this->app->json(
          ["success" => true],
          200
        );
    }

    /**
     * Logout the current user
     *
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     *
     * @api        {post} /user/logout logout
     * @apiName    logoutUser
     * @apiVersion 0.1.0
     * @apiGroup   User
     *
     * @apiSuccess {bool} success             Status of the logout
     *
     * @apiError        NotLoggedIn           You are already logged out
     **/
    public function logout(Request $request)
    {
        // check if user is already logged out
        if (!$this->getLogin()) {
            return $this->app->json(
              ["error" => "NotLoggedIn"],
              401
            );
        }

        // logout user
        $this->logoutUser();

        return $this->app->json(
          ["success" => true],
          200
        );
    }

    /**
     * Get info about the current user
     *
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     *
     * @api        {get} /user/info login info
     * @apiName    infoUser
     * @apiVersion 0.1.0
     * @apiGroup   User
     *
     * @apiSuccess {Array} user               User info about you
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "user" : {
     *               "id": Number,
     *               "email": String,
     *               "firstName" : String,
     *               "lastName" : String
     *         }
     *
     * @apiError        NotLoggedIn           You are not logged in
     **/
    public function info(Request $request)
    {
        // check if user is logged in
        if (false === $user = $this->getLogin()) {
            return $this->app->json(
              ["error" => "NotLoggedIn"],
              401
            );
        }

        return $this->app->json(
          ["user" => $user->toArray()],
          200
        );
    }
}
