<?php

namespace App\Services;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RentalService
{
    /**
     * Call sp_CreateRental to create a new rental.
     * 
     * @return object Rental record
     * @throws Exception
     */
    public function createRental(int $userId, int $carId, string $startDate, string $endDate)
    {
        try {
            $result = DB::select(
                'EXEC sp_CreateRental @user_id = ?, @car_id = ?, @start_date = ?, @end_date = ?',
                [$userId, $carId, $startDate, $endDate]
            );

            if (empty($result)) {
                throw new Exception("Failed to create rental. No record returned.");
            }

            return $result[0];
        } catch (QueryException $e) {
            // Re-throw the SQL Server RAISERROR messages to be handled by the controller
            // The SQL Server error message usually comes after the SQLSTATE
            $message = $e->getMessage();
            
            // Extract the actual SQL Server error message if possible
            if (preg_match('/\[SQL Server\](.*)/', $message, $matches)) {
                $cleanMessage = trim($matches[1]);
                // Remove the "(Connection: sqlsrv..." part that Laravel appends
                if (preg_match('/^(.*?)(?:\s*\(Connection:)/', $cleanMessage, $subMatches)) {
                    $cleanMessage = trim($subMatches[1]);
                }
                throw new Exception($cleanMessage);
            }
            
            throw new Exception("Database error occurred while processing rental.");
        }
    }

    /**
     * Call sp_ReturnRental to process a return.
     * 
     * @return object Return record
     * @throws Exception
     */
    public function returnRental(string $licensePlate, int $userId)
    {
        try {
            $result = DB::select(
                'EXEC sp_ReturnRental @license_plate = ?, @user_id = ?',
                [$licensePlate, $userId]
            );

            if (empty($result)) {
                throw new Exception("Failed to process return. No record returned.");
            }

            return $result[0];
        } catch (QueryException $e) {
            $message = $e->getMessage();
            
            if (preg_match('/\[SQL Server\](.*)/', $message, $matches)) {
                $cleanMessage = trim($matches[1]);
                if (preg_match('/^(.*?)(?:\s*\(Connection:)/', $cleanMessage, $subMatches)) {
                    $cleanMessage = trim($subMatches[1]);
                }
                throw new Exception($cleanMessage);
            }
            
            throw new Exception("Database error occurred while processing return.");
        }
    }
}
