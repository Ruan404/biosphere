<?php
namespace App\Entities;

enum Layout : string {
    case Preset = "default";
    case Auth = "auth";
    case Admin = "admin";
    case Clean = "";
}