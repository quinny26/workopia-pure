<?php

namespace App\Controllers;

use Framework\Database;

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
}