<?php

require_once __DIR__ . '/../helpers/response.php';

class DateController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    
    public function getDefaultDate() {
        $sql = "SELECT MAX(created) AS last_created FROM nominatif";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastCreated = $result['last_created'];
        $closingDate = null;
        $awalBulan   = null;

        if ($lastCreated) {
            $closingDateObj = new DateTime($lastCreated);

            // Hitung closing date: akhir bulan sebelum tanggal lastCreated
            $closingDateObj->modify('last day of previous month');
            $closingDate = $closingDateObj->format('Y-m-d');

            // Hitung awal bulan dari lastCreated
            $awalBulanObj = new DateTime($lastCreated);
            $awalBulanObj->modify('first day of this month');
            $awalBulan = $awalBulanObj->format('Y-m-d');
        }

        sendResponse(200, "Tanggal terakhir data nominatif", [
            'awal_bulan'   => $awalBulan,
            'last_created' => $lastCreated,
            'last_closing' => $closingDate
        ]);
    }

    public function getDefaultDatePH() {
        $sql = "SELECT MAX(created) AS last_created FROM nominatif_hapus_buku";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastCreated = $result['last_created'];
  

        sendResponse(200, "Tanggal terakhir data nominatif", [
            'last_created' => $lastCreated,
           
        ]);
    }


    public function getDefaultAccountHandle() {
        $sql = "SELECT MAX(created) AS last_created FROM account_handle";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastCreated = $result['last_created'];
  

        sendResponse(200, "Tanggal terakhir data nominatif", [
            'last_created' => $lastCreated,
           
        ]);
    }

    // public function getDefaultAccountHandle() {
    //     $sql = "SELECT MAX(created) AS last_created FROM account_handle";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute();
    //     $result = $stmt->fetch(PDO::FETCH_ASSOC);

    //     $lastCreated = $result['last_created'];
  

    //     sendResponse(200, "Tanggal terakhir data nominatif", [
    //         'last_created' => $lastCreated,
           
    //     ]);
    // }


    












}
