<?php

class User
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $password;
    private Role $role;
   

    public function __construct(
        string $name,
        string $email,
        string $password,
        Role $role,
        ?int $id = null
       
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
      
    }
    // ID
public function getId(): ?int {
    return $this->id;
}

public function setId(?int $id): void {
    $this->id = $id;
}


public function getName(): string {
    return $this->name;
}

public function setName(string $name): void {
    $this->name = $name;
}


public function getEmail(): string {
    return $this->email;
}

public function setEmail(string $email): void {
    $this->email = $email;
}

public function getPassword(): string {
    return $this->password;
}

public function setPassword(string $password): void {
    $this->password = $password;
}


public function getRole(): Role {
    return $this->role;
}

public function setRole(Role $role): void {
    $this->role = $role;
}


}
