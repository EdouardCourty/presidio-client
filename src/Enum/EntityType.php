<?php

declare(strict_types=1);

namespace Ecourty\PresidioClient\Enum;

enum EntityType: string
{
    case PERSON = 'PERSON';
    case PHONE_NUMBER = 'PHONE_NUMBER';
    case EMAIL_ADDRESS = 'EMAIL_ADDRESS';
    case CREDIT_CARD = 'CREDIT_CARD';
    case CRYPTO = 'CRYPTO';
    case DATE_TIME = 'DATE_TIME';
    case DOMAIN_NAME = 'DOMAIN_NAME';
    case IBAN_CODE = 'IBAN_CODE';
    case IP_ADDRESS = 'IP_ADDRESS';
    case LOCATION = 'LOCATION';
    case MEDICAL_LICENSE = 'MEDICAL_LICENSE';
    case NRP = 'NRP';
    case SG_NRIC_FIN = 'SG_NRIC_FIN';
    case UK_NHS = 'UK_NHS';
    case URL = 'URL';
    case US_BANK_NUMBER = 'US_BANK_NUMBER';
    case US_DRIVER_LICENSE = 'US_DRIVER_LICENSE';
    case US_ITIN = 'US_ITIN';
    case US_PASSPORT = 'US_PASSPORT';
    case US_SSN = 'US_SSN';
    case AU_ABN = 'AU_ABN';
    case AU_ACN = 'AU_ACN';
    case AU_TFN = 'AU_TFN';
    case AU_MEDICARE = 'AU_MEDICARE';
    case IN_PAN = 'IN_PAN';
    case IN_AADHAAR = 'IN_AADHAAR';
    case IN_VEHICLE_REGISTRATION = 'IN_VEHICLE_REGISTRATION';
    case IN_VOTER = 'IN_VOTER';
    case IN_PASSPORT = 'IN_PASSPORT';
}
