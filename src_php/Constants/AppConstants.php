<?php

namespace App\Constants;

class AppConstants
{
    // Roles de Usuario
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_DIRECTOR = 'DIRECTOR';
    public const ROLE_COORDINADOR = 'COORDINADOR';
    public const ROLE_DOCENTE = 'DOCENTE';
    public const ROLE_ESTUDIANTE = 'ESTUDIANTE';
    public const ROLE_PADRE = 'PADRE';

    // Estados de Usuario
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    // Estados de Matrícula y Admisión
    public const STATUS_PENDING = 'Pendiente';
    public const STATUS_APPROVED = 'Aprobado';
    public const STATUS_REJECTED = 'Rechazado';

    // Estados de Pago
    public const PAYMENT_PENDING = 'Pendiente';
    public const PAYMENT_PAID = 'Pagado';
    public const PAYMENT_OVERDUE = 'Vencido';
}
