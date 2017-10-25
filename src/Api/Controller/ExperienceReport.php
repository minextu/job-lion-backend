<?php namespace JobLion\Api\Controller;

use JobLion\Database\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Experience Report api controller
 */
class ExperienceReport extends AbstractController
{
    /**
     * @api        {post} /v1/experienceReport/create create
     * @apiName    createExperienceReport
     * @apiVersion 0.1.0
     * @apiGroup   Experience Report
     *
     * @apiParam {String} title             Title of report
     * @apiParam {String} text              Report text
     * @apiParam {String[]} jobCategoryIds  Job Category Ids
     *
     * @apiSuccess {bool} success           Status of the creation
     *
     * @apiError        MissingValues       Some values weren't transmited
     * @apiError        InvalidCategory     Job Category id does not exist
     * @apiError        MalformedCategories jobCategoryids is not an array
     * @apiError        NoPermissions       No Permissions to create job Categories (Not logged in)
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Create a new Experience report
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function create(Request $request) : JsonResponse
    {
        $title = $request->get('title');
        $text = $request->get('text');
        $jobCategoryIds = $request->get('jobCategoryIds');

        // check if logged in
        if (false === $user = $this->getLogin()) {
            return $this->app->json(
              ["error" => "NoPermissions"],
              401
            );
        }

        // check for missing values
        if (!$title || !$text || !$jobCategoryIds) {
            return $this->app->json(
              ["error" => "MissingValues"],
              400
            );
        }

        // check for malformed categories
        if (!is_array($jobCategoryIds)) {
            return $this->app->json(
              ["error" => "MalformedCategories"],
              400
            );
        }

        // check if job categories are valid
        $jobCategories = [];
        foreach ($jobCategoryIds as $categoryId) {
            $jobCategory = $this->entityManager
                                  ->find(Entity\JobCategory::class, $categoryId);
            $jobCategories[] = $jobCategory;

            if (!$jobCategory) {
                return $this->app->json(
                ["error" => "InvalidCategory"],
                400
              );
            }
        }

        // create report
        $report = new Entity\ExperienceReport();
        $report->setTitle($title)
               ->setText($text)
                ->setJobCategories($jobCategories)
                ->setUser($user);

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        // return success
        return $this->app->json(
          ["success" => true],
          200
        );
    }

    /**
     * @api        {get} /v1/experienceReport/list list
     * @apiName    listExperienceReport
     * @apiVersion 0.1.0
     * @apiGroup   Experience Report
     *
     * @apiSuccess {array} experienceReports  List of experience reports
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "experienceReports" : [
     *             {
     *               "id": Number
     *               "title": String,
     *               "user"
     *             }
     *         ]
     */

    /**
     * List all experience reports
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function list(Request $request) : JsonResponse
    {
        // get all experience reports

        $experienceReports = $this->entityManager
                                ->getRepository(Entity\ExperienceReport::class)
                                ->findAll();
        // get info array
        array_walk($experienceReports, function (&$value, &$key) {
            $value = $value->toArray();
        });

        // return all categories
        return $this->app->json(
          ["experienceReports" => $experienceReports],
          200
        );
    }
}
