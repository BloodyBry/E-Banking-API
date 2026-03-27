<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'DEPOSIT';
    case WITHDRAWAL = 'WITHDRAWAL';
    case TRANSFER_IN = 'TRANSFER_IN';
    case TRANSFER_OUT = 'TRANSFER_OUT';
    case FEE = 'FEE';
    case FEE_FAILED = 'FEE_FAILED';
    case INTEREST = 'INTEREST';
}