<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;

class ListingController {

    protected $db;

    public function __construct() {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    /**
     * show all listings
     * 
     * @return void
     */

    public function index(){

        $listings = $this->db->query('SELECT * FROM listings')->fetchAll();

        loadView('listings/index', [
            'listings'=> $listings
        ]);
    }

    /**
     * show the create listing form
     * 
     * @return void
     */

    public function create(){
        loadView('listings/create');
    }

    /**
     * show a single listing
     * 
     * @param array $params
     * 
     * @return void
     */

    public function show($params){
        $id = $params['id'] ??  '';

        $params = [
            'id'=> $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        //check if listing exits
        if(!$listing){
            ErrorController::notFound('Listing not Found');
            return;
        }

        loadView('listings/show', [
            'listing' => $listing
        ]);
    }

    
/**
 * Store data in database
 * 
 * @return void
 */

 public function store(){
    $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

    $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

    $newListingData['user_id'] = 1;

    $newListingData = array_map('sanitize', $newListingData);

    $requiredFields = ['title', 'description', 'email', 'city', 'state', 'salary'];

    $errors = [];
    
    foreach($requiredFields as $fields){
        if(empty($newListingData[$fields]) || !Validation::string($newListingData[$fields])){
            $errors[$fields] = ucfirst($fields). ' is required';
        }
    }

    if(!empty($errors)){
        //reload view with errors
        loadView('listings/create', [
            'errors'=> $errors, 
            'listing'=> $newListingData
        ]);
    }
    else{
        //submit data

        $fields = [];

        foreach($newListingData as $field => $value){
            $fields[] = $field;
        }
        $fields = implode(', ', $fields);

        $value = [];

        foreach($newListingData as $field => $value){
            //convert empty strings to null
            if($value === ''){
                $newListingData[$field] = null;
            }
            $values[] = ':'. $field;
        }

        $values = implode(', ', $values);

        $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";
        $this->db->query($query, $newListingData);

        redirect('/listings');
    }
 }

 /** 
  * delete a listing 
  *
  * @param array $params 
  * @return void 
  */

  public function destroy($params){
    $id = $params['id'];

    $params = [
        'id'=> $id
    ];

    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

    if(!$listing){
        ErrorController::notFound('Listing not found');
        return;
    }

    $this->db->query('DELETE FROM listings WHERE id = :id', $params);

    //set flash message
    $_SESSION['success_message'] = 'Listing deleted successfully';

    redirect('/listings');
  }

   /**
     * show the listing edit form
     * 
     * @param array $params
     * 
     * @return void
     */

     public function edit($params){
        $id = $params['id'] ??  '';

        $params = [
            'id'=> $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        //check if listing exits
        if(!$listing){
            ErrorController::notFound('Listing not Found');
            return;
        }

        loadView('listings/edit', [
            'listing' => $listing
        ]);
    }

    /**
     * update a listing 
     * 
     * @param array $params
     * @return void
     */

     public function update($params){
        $id = $params['id'] ??  '';

        $params = [
            'id'=> $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        //check if listing exits
        if(!$listing){
            ErrorController::notFound('Listing not Found');
            return;
        }

        $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

        $updateValues = [];

        $updateValues = array_intersect_key($_POST, array_flip($allowedFields));

        $updateValues = array_map('sanitize', $updateValues);

        $requiredFields = ['title', 'description', 'salary', 'email', 'city', 'state'];

        $errors = [];

        foreach ($requiredFields as $field){
            if(empty($updateValues[$field]) || !Validation::string($updateValues[$field])){
                $errors[$field] = ucfirst($field). ' is required';
            }
        }

        if(!empty($errors)){
            loadView('listings/edit', [
                'listing'=> $listing,
                'errors'=>$errors
            ]);
            exit;
        } else{
            //submit to database
            $updateFields = [];

            foreach (array_keys($updateValues) as $field) {
                $updateFields[] = "{$field} = :{$field}";
            }
                
                $updateFields = implode(', ', $updateFields);
        
                $updateQuery = "UPDATE listings SET $updateFields WHERE id = :id";
        
                $updateValues['id'] = $id;
                $this->db->query($updateQuery, $updateValues);
        
                $_SESSION['success_message'] = 'Listing updated successfully';
        
                redirect('/listings/' . $id);
            }
        }
     }
