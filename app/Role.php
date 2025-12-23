<?php

namespace App;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case CompanyOwner = 'company_owner';
    case Employee = 'employee';
    case Technician = 'technician';
}
