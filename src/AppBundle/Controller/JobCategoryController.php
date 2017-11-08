<?php namespace JobLion\AppBundle\Controller;

use JobLion\AppBundle\Controller\AbstractController;
use JobLion\AppBundle\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * JobCategory api controller
 */
class JobCategoryController extends AbstractController
{
    /**
     * @api        {post} /v1/jobCategory/create create
     * @apiName    createJobCategory
     * @apiVersion 0.1.0
     * @apiGroup   Job Category
     *
     * @apiParam {String} name           Name of Job Category
     *
     * @apiSuccess {bool} success        Status of the creation
     *
     * @apiError        MissingValues    Some values weren't transmited
     * @apiError        CategoryExists   A job category with this name already exists
     *
     * @apiUse Login
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Create a new Job Category
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function create(Request $request) : JsonResponse
    {
        // check if logged in
        $error = $this->requireLogin($request);
        if ($error) {
            return $error;
        }

        $name = $request->get('name');

        // check for missing values
        if (!$name) {
            return $this->app->json(
              ["error" => "MissingValues"],
              400
            );
        }

        // check if category already exists
        $testCategory = $this->entityManager
                                ->getRepository(Entity\JobCategory::class)
                                ->findOneBy(array('name' => $name));
        if ($testCategory) {
            return $this->app->json(
              ["error" => "CategoryExists"],
              409
            );
        }

        // create job category
        $jobCategory = new Entity\JobCategory();
        $jobCategory->setName($name)
                    ->setUser($this->user);

        $this->entityManager->persist($jobCategory);
        $this->entityManager->flush();

        // return success
        return $this->app->json(
          ["success" => true],
          200
        );
    }

    /**
     * @api        {get} /v1/jobCategory/list list
     * @apiName    listJobCategories
     * @apiVersion 0.1.0
     * @apiGroup   Job Category
     *
     * @apiSuccess {array} jobCategories  List of job categories
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "jobCategories" : [
     *             {
     *               "id": Number
     *               "name": String
     *             }
     *         ]
     */

    /**
     * List all job categories
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function list(Request $request) : JsonResponse
    {
        // get all job categories

        $jobCategories = $this->entityManager
                                ->getRepository(Entity\JobCategory::class)
                                ->findAll();
        // get info array
        array_walk($jobCategories, function (&$value, &$key) {
            $value = $value->toArray();
        });

        // return all categories
        return $this->app->json(
          ["jobCategories" => $jobCategories],
          200
        );
    }
}
