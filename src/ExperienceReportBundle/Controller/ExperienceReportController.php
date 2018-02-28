<?php namespace JobLion\ExperienceReportBundle\Controller;

use JobLion\AppBundle\Controller\AbstractController;
use JobLion\ExperienceReportBundle\Entity;
use JobLion\AppBundle;
use JobLion\CompanyBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Experience Report api controller
 */
class ExperienceReportController extends AbstractController
{
    /**
     * @api        {post} /v1/experienceReports/ create
     * @apiName    createExperienceReport
     * @apiVersion 0.1.0
     * @apiGroup   Experience Report
     *
     * @apiParam {String} title             Title of report
     * @apiParam {String} text              Report text
     * @apiParam {Number[]} jobCategoryIds  Job Category Ids
     * @apiParam {Number} [companyId]       Id of company this report is associated with
     *
     * @apiSuccess {Number} id              Id of the newly created report
     *
     * @apiError        MissingValues       Some values weren't transmited
     * @apiError        InvalidCategory     Job Category id does not exist
     * @apiError        InvalidCompany      companyId is invalid
     *
     * @apiUse Login
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
        // check if logged in
        $error = $this->requireLogin($request);
        if ($error) {
            return $error;
        }

        $title = $request->get('title');
        $text = $request->get('text');
        $jobCategoryIds = $request->get('jobCategoryIds');
        $companyId = $request->get('companyId');

        // allow comma separated list
        if (!is_array($jobCategoryIds)) {
            $jobCategoryIds = explode(',', $jobCategoryIds);
        }

        // check for missing values
        if (!$title || !$text || !$jobCategoryIds) {
            return $this->app->json(
              ["error" => "MissingValues"],
              400
            );
        }

        // check if job categories are valid
        $jobCategories = [];
        foreach ($jobCategoryIds as $categoryId) {
            $jobCategory = $this->entityManager
                                  ->find(AppBundle\Entity\JobCategory::class, $categoryId);
            $jobCategories[] = $jobCategory;

            if (!$jobCategory) {
                return $this->app->json(
                ["error" => "InvalidCategory"],
                400
              );
            }
        }

        // check if companyId is valid
        $company = null;
        if ($companyId) {
            $company = $this->entityManager
                            ->find(Company::class, $companyId);
            if (!$company) {
                return $this->app->json(
                  ["error" => "InvalidCompany"],
                  400
                );
            }
        }

        // create report
        $report = new Entity\ExperienceReport();
        $report->setTitle($title)
               ->setText($text)
               ->setJobCategories($jobCategories)
               ->setCompany($company)
               ->setUser($this->user);

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        // return success
        $response = new JsonResponse(["id" => $report->getId()], 201);
        $url = $this->generateUrl('experienceReports', $report->getId());
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * @api        {get} /v1/experienceReports/ list
     * @apiName    listExperienceReport
     * @apiVersion 0.1.0
     * @apiGroup   Experience Report
     *
     * @apiParam {Number} [jobCategoryIds]     Ids of job category to show reports for (empty to show reports for all categories)
     * @apiParam {Number} [offset=0]           Number of entries to skip
     * @apiParam {Number} [limit=0]            Number of entries to show (0 for all)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         reports: [
     *             {
     *               "id": Number,
     *               "title": String,
     *               "text": String,
     *               "jobCategories": [
     *                   {
     *                      "id": Number,
     *                      "name": String,
     *                   }
     *               ],
     *               "company": {
     *                 "id": Number,
     *                 "title": String
     *               },
     *               "user": {
     *                 "id": Number,
     *                 "email": String,
     *                 "firstName": String,
     *                 "lastName": String
     *               },
     *               "created": String
     *             }
     *         ],
     *         total: Number
     *     }
     */

    /**
     * List all experience reports
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function list(Request $request) : JsonResponse
    {
        $jobCategoryIds = $request->get('jobCategoryIds');
        $offset = $request->get('offset');
        $limit = $request->get('limit');

        // allow comma separated list
        if (!empty($jobCategoryIds) && !is_array($jobCategoryIds)) {
            $jobCategoryIds = explode(',', $jobCategoryIds);
        }

        // get all experience reports

        $experienceReports = $this->entityManager
                                ->getRepository(Entity\ExperienceReport::class)
                                ->findByJobCategories($jobCategoryIds, $offset, $limit);

        // get total
        $total = count($experienceReports);

        // get info array
        $reportInfos = [];
        foreach ($experienceReports as $report) {
            $reportInfos[] = $report->toArray();
        }

        // return all categories
        return $this->app->json(
          [
            "reports" => $reportInfos,
            "total" => $total
          ],
          200
        );
    }

    /**
     * @api        {get} /v1/experienceReports/:id get
     * @apiName    getExperienceReport
     * @apiVersion 0.1.0
     * @apiGroup   Experience Report
     *
     * @apiParam {Number} id        Id of job category
     *
     * @apiError          NotFound  Job Category not found
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         {
     *           "id": Number,
     *           "title": String,
     *           "text": String,
     *           "user": {
     *             "id": Number,
     *             "email": String,
     *             "firstName": String,
     *             "lastName": String
     *            },
     *            "created": String
     *          }
     */

    /**
     * Get the experience report with the given id
     * @param  Request $request Info about this request
     * @param  int     $id      Id of report
     * @return JsonResponse     Response in json format
     */
    public function get(Request $request, $id) : JsonResponse
    {
        // get experience report with given id

        $experienceReport = $this->entityManager
                              ->find(Entity\ExperienceReport::class, $id);

        if (!$experienceReport) {
            return $this->app->json(
              ["error" => "NotFound"],
              404
            );
        }

        // return report
        return $this->app->json(
          $experienceReport->toArray(),
          200
        );
    }
}
