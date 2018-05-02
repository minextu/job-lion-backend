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
     * @api        {post} /v1/jobCategories/ create
     * @apiName    createJobCategory
     * @apiVersion 0.1.0
     * @apiGroup   Job Category
     *
     * @apiParam {String} name           Name of Job Category
     *
     * @apiSuccess {Number} id           Id of the newly created category
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
        $response = new JsonResponse(["id" => $jobCategory->getId()], 201);
        $url = $this->generateUrl('jobCategories', $jobCategory->getId());
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * @api        {get} /v1/jobCategories/ list
     * @apiName    listJobCategories
     * @apiVersion 0.1.0
     * @apiGroup   Job Category
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       [
     *         {
     *           "id": Number
     *           "name": String,
     *           "user": {
     *             "id" : Number,
     *             "email": String,
     *             "firstName": String,
     *             "lastName" : String
     *            },
     *            "created": String
     *          }
     *       ]
     *     }
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
          $jobCategories,
          200
        );
    }

    /**
     * @api        {get} /v1/jobCategories/:id get
     * @apiName    getJobCategory
     * @apiVersion 0.1.0
     * @apiGroup   Job Category
     *
     * @apiParam {Number} id        Id of job category
     *
     * @apiError          NotFound  Job Category not found
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": Number
     *       "name": String,
     *       "user": {
     *         "id" : Number,
     *         "email": String,
     *         "firstName": String,
     *         "lastName" : String
     *       },
     *       "created": String
     *     }
     */

    /**
     * Return the given job category
     * @param  Request $request Info about this request
     * @param  int     $id      Job Category id
     * @return JsonResponse     Response in json format
     */
    public function get(Request $request, $id) : JsonResponse
    {
        // get given job category
        $jobCategory = $this->entityManager
                            ->find(Entity\JobCategory::class, $id);

        if (!$jobCategory) {
            return $this->app->json(
              ["error" => "NotFound"],
              404
            );
        }

        // return all categories
        return $this->app->json(
          $jobCategory->toArray(),
          200
        );
    }

    /**
     * @api        {delete} /v1/jobCategories/:id delete
     * @apiName    deleteJobCategory
     * @apiVersion 0.1.0
     * @apiGroup   Job Category
     *
     * @apiParam {Number} id        Category id
     *
     * @apiError          NotFound  Category not found
     * @apiUse Login
     * @apiUse AdminOnly
     *
     */

    /**
     * delete the category with the given id
     * @param  Request $request Info about this request
     * @param  int     $id      Id of category
     * @return JsonResponse     Response in json format
     */
    public function delete(Request $request, $id) : JsonResponse
    {
        // check for permissions
        $error = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        // get experience report with given id
        $category = $this->entityManager
                              ->find(Entity\JobCategory::class, $id);

        if (!$category) {
            return $this->app->json(
              ["error" => "NotFound"],
              404
            );
        }

        // remove this report
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        // return success
        return $this->app->json(
          ["success" => true],
          200
        );
    }
}
