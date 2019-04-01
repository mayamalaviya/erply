<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @OA\OpenApi(
 *   @OA\Server(
 *        description="Api server",
 *        url="http://localhost:8081/api/v1",
 *    ),
 *    @OA\Info(
 *        version="1",
 *        title="Product Api",
 *        description="This project is an exmaple of a small API written in PHP",
 *    ),
 * )
 */

 /**
    * @OA\Schema(
    *    schema="Product",
    *    description="Product model",
    *    title="Product model",
    *    required={"name", "price"},
    * )

    * @OA\Property(
    *    description="Product name",
    *    title="Product name"
    * )
    * 
    * @var string
*/

/**
 * @OA\Property(
 *    description="Product price",
 *    title="Product price"
 * )
 * 
 * @var integer
 */
// Routes

$app->group('/api/v1', function () {

    /**
     * @OA\Get(
     *      path="/",
     *      summary="Welcome message",
     *
     *      @OA\Response(
     *          response="200",
     *          description="success"
     *      ) 
     * )  
     **/ 
    $this->map(['GET'], '', function (Request $request, Response $response) {
        return $response->withJson(['message' => 'Welcome to Product API']);
    });

    /**
     * @OA\Get(
     *      path="/products",
     *      summary="Request all products",
     *      description="Returns all products",
     *      operationId="getProducts",
     * 
     *      @OA\Response(
     *          response="200",
     *          description="success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  ref="#/components/schemas/Product"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Products not found"
     *      )
     * ) 
     **/    
    $this->get('/products', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT * FROM products ORDER BY name");
        $sth->execute();
        $products = $sth->fetchAll();
        return $this->response->withJson($products);
    });

    /**
     *  @OA\Get(
     *      path="/products/{id}",
     *      summary="Find product by ID",
     *      description="Returns a single product",
     *      operationId="getProductById",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *      ),
     *      @OA\Response(response="200",
     *          description="success",
     *          @OA\JsonContent(ref="#/components/schemas/Product"),
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid ID supplied"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Product not found"
     *      )
     * )
     */
    $this->get('/products/[{id}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT * FROM products WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $products = $sth->fetchObject();
        return $this->response->withJson($products);
    });


    /**
     *  @OA\Get(
     *      path="/products/search/{name}",
     *      summary="Find product by name",
     *      description="Returns one or more products",
     *      operationId="getProductByName",
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          required=true,
     *      ),
     *      @OA\Response(response="200",
     *          description="success",
     *          @OA\JsonContent(ref="#/components/schemas/Product"  ),
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid name supplied"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Product not found"
     *      )
     * )
     */
    $this->get('/products/search/[{name}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT * FROM products WHERE UPPER(name) LIKE :name ORDER BY name");
        $name = "%".$args['name']."%";
        $sth->bindParam("name", $name);
        $sth->execute();
        $products = $sth->fetchAll();
        return $this->response->withJson($products);
    });
    
    // Add a product with name and price
    /**
     * @OA\Post(
     *      path="/products",
     *      operationId="addProduct",
     *      summary="Add a new product",
     *      @OA\RequestBody(
     *          description="Product object that needs to be added",
     *          required=true,
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Product"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Invalid input"
     *      ),
     *      @OA\Response(response="200",
     *          description="success",
     *          @OA\JsonContent(),
     *      ),
     * )
     */
    $this->post('/products', function ($request, $response) {
        $input = $request->getParsedBody();
        $sql = "INSERT INTO products (product) VALUES (:product)";
         $sth = $this->db->prepare($sql);
        $sth->bindParam("product", $input['product']);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
    });
    
    // Delete a product with given id
    /**
     * @OA\Delete(
     *      path="products/{id}",
     *      operationId="deleteProduct",
     *      summary="Delete a product",
     *      @OA\Parameter(
     *          description="Product ID to delete",
     *          in="path",
     *          name="id",
     *          required=true,
     *      ),
     *
     *      @OA\Response(response="200",
     *          description="success",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID supplied" 
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
     */
    $this->delete('/products/[{id}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("DELETE FROM products WHERE id=:id");
       $sth->bindParam("id", $args['id']);
       $sth->execute();
       $products = $sth->fetchAll();
       return $this->response->withJson($products);
    });
        
    // Update product with given id
    /**
     * @OA\Post(
     *      path="/products/{id}",
     *      operationId="updateProduct",
     *      summary="Update an existing product",
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="name",
     *                  description="Updated name of the product",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="price",
     *                  description="Updated price of the product",
     *                  type="decimal"
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the product that needs to be updated",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(response="200",
     *          description="success",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      ),
     *      @OA\Response(
     *          response="405",
     *          description="Invalid input"
     *      )
     * )
     */
    $this->put('/products/[{id}]', function ($request, $response, $args) {
        $input = $request->getParsedBody();
        $sql = "UPDATE products SET product=:product WHERE id=:id";
         $sth = $this->db->prepare($sql);
        $sth->bindParam("id", $args['id']);
        $sth->bindParam("product", $input['product']);
        $sth->execute();
        $input['id'] = $args['id'];
        return $this->response->withJson($input);
    });

});

