<?php namespace JobLion\Api;

use JobLion\Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class User extends AbstractApi
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
        } catch (\Exception $e) {
            return $this->app->json(
              ["error" => "UnknownError", "message" => $e->getMessage()],
              500
            );
        }
    }
}
