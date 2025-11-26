<?php

class User
{
    private $id;
    private $first_name;
    private $last_name;
    private $email;
    private $phone;
    private $password;
    private $role;
    private $department;
    private $reset_token;
    private $reset_expires;

    public function __construct($first_name, $last_name, $email, $phone, $password, $role, $department)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->role = $role;
        $this->department = $department;
        $this->reset_token = null;
        $this->reset_expires = null;
    }

    // --- GETTERS ---
    public function getId() { return $this->id; }
    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getRole() { return $this->role; }
    public function getDepartment() { return $this->department; }
    public function getPassword() { return $this->password; }
    public function getResetToken() { return $this->reset_token; }
    public function getResetExpires() { return $this->reset_expires; }

    // --- SETTERS ---
    public function setPassword($password) { $this->password = $password; }
    public function setResetToken($token) { $this->reset_token = $token; }
    public function setResetExpires($datetime) { $this->reset_expires = $datetime; }
}
?>