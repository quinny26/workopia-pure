<?php 

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;

class UserController {
  protected $db;

  public function __construct(){
    $config = require basePath('config/db.php');
    $this->db = new Database($config);
  }
    /**
     * this will show the login page 
     * 
     * @return void
     */

     public function login(){
        loadView('users/login');
     }

    /**
     * this will show the register page 
     * 
     * @return void
     */

     public function create(){
        loadView('users/create');
     }

     /**
      * store user in database 
      *
      *@return void
      */

      public function store(){
         $name = $_POST['name'];
         $email = $_POST['email'];
         $city = $_POST['city'];
         $state = $_POST['state'];
         $password = $_POST['password'];
         $passwordConfirmation = $_POST['password_confirmation'];

         $errors = [];

         //validation 
         if(!Validation::email($email)){
            $errors['email'] = 'Please enter a valid email address';
         }

         if(!Validation::string($name, 2, 50)){
            $errors['name'] = 'Name must be between 2 and 50 characters';
         }

         if(!Validation::string($password, 6, 50)){
            $errors['password'] = 'Password must be at least 6 characters';
         }

         if(!Validation::match($password, $passwordConfirmation)){
            $errors['password_confirmation'] = 'Passwords do not match';
         }

         if(!empty($errors)){
            loadView('users/create', [
               'errors'=> $errors,
               'user'=> [
                  'name'=> $name,
                  'email'=> $email,
                  'city'=> $city,
                  'state'=> $state
               ]
               ]);
               exit;
         }

         //check if email exists
         $params = [
            'email'=> $email
         ];

         $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

         if($user){
            $errors['email'] = 'That email already exists';
            loadView('users/create', [
               'errors'=> $errors
            ]);
            exit;
         }

         //create user account 
         $params = [
            'name'=> $name,
            'email'=> $email,
            'city'=> $city,
            'state'=> $state,
            'password'=> password_hash($password, PASSWORD_DEFAULT)
         ];

         $this->db->query('INSERT INTO users (name, email, city, state, password) VALUES (:name, :email, :city, :state, :password)', $params);

         //get new user id 
         $userId = $this->db->conn->lastInsertId();

         //set user session
         Session::set('user', [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'city' => $city,
            'state' => $state
         ]); 

         redirect('/');
      }

      /**
       * logout a user and kill session 
       * 
       * @return void 
       */

       public function logout(){
         Session::clearAll();

         $params = session_get_cookie_params();
         setcookie('PHPSESSID', '', time() -86400, $params['path'], $params['domain']);

         redirect('/');
       }

       /**
        * authenticate a user with an email and password
        *
        * @return void
        */

        public function authenticate(){
         $email = $_POST['email'];
         $password = $_POST['password'];

         $errors = [];

         /**
          * validation
          */

          if(!Validation::email($email)) {
            $errors['email'] = 'Please enter a valid email';
          }

          if(!Validation::string($password, 6, 50)) {
            $errors['password'] = 'Password must be at least 6 characters';
          }

          //check for errors
        if(!empty($errors)) {
         loadView('users/login', [
            'errors'=> $errors
         ]);
         exit;
        }

        //check for email 
        $params = [
         'email'=> $email
        ]; 

        $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

        if(!$user){
         $errors['email'] = 'Incorrect credentials';
         loadView('users/login', [
            'errors'=> $errors
         ]);
         exit;
        }

        //check if password is correct 

        if(!password_verify($password, $user->password)){
         $errors['password'] = 'Incorrect credentials';
         loadView('users/login', [
            'errors'=> $errors
         ]);
         exit;
        }

        //set user session 
        Session::set('user', [
         'id' => $user->id,
         'name' => $user->name,
         'email' => $user->email,
         'city' => $user->city,
         'state' => $user->state
      ]); 

      redirect('/');
   }
}