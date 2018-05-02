<?php namespace JobLion\CompanyBundle\Controller;

use JobLion\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use JobLion\CompanyBundle\Entity\Company;

/**
 * Company api controller
 */
class CompanyController extends AbstractController
{
    /**
     * @api        {post} /v1/companies/ create
     * @apiName    createCompany
     * @apiVersion 0.1.0
     * @apiGroup   Company
     *
     * @apiParam   {String} title           Company Title
     *
     * @apiSuccess {Number} id              Id of the newly created company
     *
     * @apiError   MissingValues            Some values weren't transmited
     * @apiError   CompanyExists            This company was already added
     *
     * @apiUse Login
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Create a new Company
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

        // check for missing values
        if (!$title) {
            return $this->app->json(
            ["error" => "MissingValues"],
            400
          );
        }

        // check if company already exists
        $testCompany = $this->entityManager
                                ->getRepository(Company::class)
                                ->findOneBy(array('title' => $title));
        if ($testCompany) {
            return $this->app->json(
              ["error" => "CompanyExists"],
              409
            );
        }

        // add the company
        $company = new Company();
        $company->setTitle($title)
                ->setUser($this->user);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        // return success
        $response = new JsonResponse(["id" => $company->getId()], 201);
        $url = $this->generateUrl(
          "companies",
          $company->getId()
        );
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * @api        {get} /v1/companies list
     * @apiName    listCompanies
     * @apiVersion 0.1.0
     * @apiGroup   Company
     *
     * @apiParam {Number} [offset=0]           Number of entries to skip
     * @apiParam {Number} [limit=0]            Number of entries to show (0 for all)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         companies: [
     *             {
     *               "id": Number,
     *               "title": String,
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
     * List all companies
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function list(Request $request) : JsonResponse
    {
        $offset = $request->get('offset');
        $limit = $request->get('limit');

        // get all companies

        $companies = $this->entityManager
                          ->getRepository(Company::class)
                          ->findAll($offset, $limit);

        // get total
        $total = count($companies);

        // get info array
        $companyInfos = [];
        foreach ($companies as $company) {
            $companyInfos[] = $company->toArray();
        }

        // return all companies
        return $this->app->json(
          [
            "companies" => $companyInfos,
            "total" => $total
          ],
          200
        );
    }


    /**
     * @api        {get} /v1/companies/:id get
     * @apiName    getCompany
     * @apiVersion 0.1.0
     * @apiGroup   Company
     *
     * @apiParam {Number} id        Id of company
     *
     * @apiError          NotFound  Company not found
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         {
     *           "id": Number,
     *           "title": String,
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
     * Get the company with the given id
     * @param  Request $request Info about this request
     * @param  int     $id      Id of report
     * @return JsonResponse     Response in json format
     */
    public function get(Request $request, $id) : JsonResponse
    {
        // get company with given id

        $company = $this->entityManager
                        ->find(Company::class, $id);

        if (!$company) {
            return $this->app->json(
              ["error" => "NotFound"],
              404
            );
        }

        // return report
        return $this->app->json(
              $company->toArray(),
              200
            );
    }

    /**
     * @api        {delete} /v1/companies/:id delete
     * @apiName    deleteCompany
     * @apiVersion 0.1.0
     * @apiGroup   Company
     *
     * @apiParam {Number} id        Company id
     *
     * @apiError          NotFound  Company not found
     * @apiUse Login
     * @apiUse AdminOnly
     *
     */

    /**
     * delete the company with the given id
     * @param  Request $request Info about this request
     * @param  int     $id      Id of company
     * @return JsonResponse     Response in json format
     */
    public function delete(Request $request, $id) : JsonResponse
    {
        // check for permissions
        $error = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        // get the company with given id
        $company = $this->entityManager
                              ->find(Company::class, $id);

        if (!$company) {
            return $this->app->json(
              ["error" => "NotFound"],
              404
            );
        }

        // remove this report
        $this->entityManager->remove($company);
        $this->entityManager->flush();

        // return success
        return $this->app->json(
          ["success" => true],
          200
        );
    }
}
