<?php
namespace App\Entities;

enum Role : string {
    case Admin = "admin";
    case User = "user";
}