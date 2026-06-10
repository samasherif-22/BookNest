<?php


class PayoutLedger
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Get all payout records for a specific store.
     
    public function getByStore(int $storeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT pl.*, o.status AS order_status
             FROM payout_ledger pl LEFT JOIN orders o ON o.id = pl.order_id
             WHERE pl.store_id = :sid ORDER BY pl.id DESC"
        );
        $stmt->execute([':sid' => $storeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
      //Get total unpaid vendor net for a store (for dashboard summary).
     
    public function getPendingTotal(int $storeId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(vendor_net), 0) FROM payout_ledger
             WHERE store_id = :sid AND paid_out = 0"
        );
        $stmt->execute([':sid' => $storeId]);
        return (float)$stmt->fetchColumn();
    }

   
      //Insert a new payout record after an order is placed.
     
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO payout_ledger
             (order_id, store_id, gross_amount, commission_rate, commission_amt, vendor_net, paid_out)
             VALUES (:order_id, :store_id, :gross_amount, :commission_rate, :commission_amt, :vendor_net, 0)"
        );
        return $stmt->execute($data);
    }

    
     // Get summary totals for the store report.
   
    public function getSummary(int $storeId = 0): array
    {
        
        $sql = "SELECT
                   COALESCE(SUM(gross_amount), 0)    AS total_gross,
                   COALESCE(SUM(commission_amt), 0)  AS total_commission,
                   COALESCE(SUM(vendor_net), 0)      AS total_net
                 FROM payout_ledger";
        
        if ($storeId > 0) {
            $sql .= " WHERE store_id = :sid";
        }

        $stmt = $this->db->prepare($sql);
        
        if ($storeId > 0) {
            $stmt->execute([':sid' => $storeId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}