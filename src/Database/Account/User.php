<?php namespace JobLion\Database\Account;

use Hautelook\Phpass\PasswordHash;
use JobLion\Database\Exception;
use JobLion\Database\Backend\BackendInterface;
use PDO;

/**
 * Can Create and Load Info about a User using a Database
 */
class User
{
    /**
     * Main database
     *
     * @var BackendInterface
     */
    private $db;

    /**
     * Unique User Id
     *
     * @var int
     */
    private $id;

    /**
     * Users first name
     *
     * @var string
     */
    private $first_name;

    /**
     * Users last name
     *
     * @var string
     */
    private $last_name;

    /**
     * User E-Mail
     *
     * @var string
     */
    private $email;

    /**
     * Hashed User Password
     *
     * @var string
     */
    private $hash;

    /**
     * Creates a new Instance. Loads User Info when $id is specified
     *
     * @param BackendInterface $db Database backend to be used
     * @param int               $id User Id to be loaded
     */
    public function __construct(BackendInterface $db, $id=false)
    {
        $this->db = $db;

        if ($id !== false) {
            $status = $this->loadId($id);
            if ($status === false) {
                throw new Exception\InvalidId("Invalid User ID '" . $id . "'");
            }
        }
    }


    /**
     * Load User Info using an email
     *
     * @param  string $email User email to search for
     * @return bool             True if User could be found, False otherwise
     */
    public function loadEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email=?';
        $stmt = $this->db->getPdo()->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user === false) {
            return false;
        }

        return $this->load($user);
    }

    /**
     * Load User Info using the unique Id
     *
     * @param  int $id Unique User Id
     * @return bool        True if User could be found, False otherwise
     */
    public function loadId($id)
    {
        $sql = 'SELECT * FROM users WHERE id=?';
        $stmt = $this->db->getPdo()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user === false) {
            return false;
        }

        return $this->load($user);
    }

    /**
     * Get the Unique User Id
     *
     * @return int   Unique User Id
     */
    public function getId()
    {
        if (!isset($this->id)) {
            throw new Exception\Exception("User has to be loaded first.");
        }

        return $this->id;
    }

    /**
     * Get Users first name
     *
     * @return string   Users first name
     */
    public function getFirstName()
    {
        if (!isset($this->firstName)) {
            throw new Exception\Exception("User has to be loaded first.");
        }

        return $this->firstName;
    }

    /**
     * Get Users last name
     *
     * @return string   Users last name
     */
    public function getLastName()
    {
        if (!isset($this->lastName)) {
            throw new Exception\Exception("User has to be loaded first.");
        }

        return $this->lastName;
    }

    /**
     * Get User E-Mail
     *
     * @return string   User E-Mail
     */
    public function getEmail()
    {
        if (!isset($this->id)) {
            throw new Exception\Exception("User has to be loaded first.");
        }

        return $this->email;
    }

    /**
     * Set User E-Mail
     *
     * @param string $email New User E-Mail
     *
     * @return User
     */
    public function setEmail($email)
    {
        $testUser = new User($this->db);
        $exists = $testUser->loadEmail($email);

        if ($exists !== false) {
            throw new Exception\EmailExists("Email does already exist.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception\InvalidEmail("Invalid email format.");
        }

        $this->email = $email;
        return $this;
    }

    /**
     * Set Users first name
     *
     * @param string $firstName new first name
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Set Users last name
     *
     * @param string $lastName new last name
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Set User Password
     *
     * Throws InvalidPasswordException on invalid Passwords
     *
     * @param string $password New User Password
     *
     * @return User
     */
    public function setPassword($password)
    {
        if (strlen($password) < 6) {
            throw new Exception\InvalidPassword("Password is too short");
        }

        $this->hash = $this->hashPassword($password);
        return $this;
    }

    /**
     * Assign Values to all private attributes using a user array
     *
     * @param  array $user User Array created by a Database Object
     * @return bool            True on success, False otherwise
     */
    private function load(array $user)
    {
        $this->id = $user['id'];
        $this->email = $user['email'];
        $this->firstName = $user['first_name'];
        $this->lastName = $user['last_name'];
        $this->hash = $user['hash'];

        return true;
    }

    /**
     * Check if the given Password is correct for this User
     *
     * @param  string $password Password to be checked
     * @return bool                 True if the Password is correct, False otherwise
     */
    public function checkPassword($password)
    {
        if (!isset($this->hash)) {
            throw new Exception\Exception("User has to be loaded first.");
        }

        $hasher = new PasswordHash(8, false);
        $check = $hasher->CheckPassword($password, $this->hash);

        return $check;
    }

    /**
     * Save User in Database
     *
     * @return User
     */
    public function create()
    {
        if (isset($this->id)) {
            throw new Exception\Exception("User was loaded and is not allowed to be recreated.");
        }
        if (empty($this->email)) {
            throw new Exception\Exception("E-Mail has to set via setEmail first.");
        }
        if (empty($this->firstName)) {
            throw new Exception\Exception("First name has to set via setFirstName first.");
        }
        if (empty($this->lastName)) {
            throw new Exception\Exception("Last name has to set via setLastName first.");
        }
        if (empty($this->hash)) {
            throw new Exception\Exception("Password has to set via setPassword first.");
        }

        $sql = 'INSERT into users
                (email, first_name, last_name, hash)
                VALUES (?, ?, ?, ?)';
        $stmt = $this->db->getPdo()->prepare($sql);
        $status = $stmt->execute(
        [
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->hash
        ]
        );

        if ($status) {
            $this->id = $status;
        } else {
            throw new Exception\Exception("User could not be saved to Database");
        }
    }

    /**
     * Hash Password string using Hautelook\Phpass
     *
     * @param  string $password Password to be hashed
     * @return string           Hashed Password
     */
    private function hashPassword($password)
    {
        $hasher = new PasswordHash(8, false);
        $hash = $hasher->HashPassword($password);
        if (strlen($hash) >= 20) {
            return $hash;
        } else {
            throw new Exception\Exception("Invalid Hash");
        }
    }

    /**
     * Convert object to info array
     * @return array info array
     */
    public function toArray()
    {
        if (!isset($this->id)) {
            throw new Exception\Exception("User has to be loaded first.");
        }

        $arr = [
          "id" => $this->getId(),
          "email" => $this->getEmail(),
          "firstName" => $this->getFirstName(),
          "lastName" => $this->getLastName()
        ];

        return $arr;
    }
}
