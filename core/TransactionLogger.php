<?php
/**
 * TRANSACTION LOGGER - Immutable Ledger Engine
 * Handles historical checkpoints for every stock movement.
 */

class TransactionLogger {
    private $pdo_conn;

    public function __construct($db_connection) {
        $this->pdo_conn = $db_connection;
    }

    /**
     * Commits a stock movement record to the immutable ledger.
     */
    public function log_movement($item_id, $type, $qty, $price, $user_id, $notes = '') {
        $sql_insert = "INSERT INTO transactions 
                       (item_id, trans_type, quantity, unit_price, performed_by, reference_notes) 
                       VALUES (:item_id, :type, :qty, :price, :user, :notes)";
        
        $stmt_handle = $this->pdo_conn->prepare($sql_insert);
        return $stmt_handle->execute([
            'item_id' => $item_id,
            'type'    => $type,
            'qty'     => $qty,
            'price'   => $price,
            'user'    => $user_id,
            'notes'   => $notes
        ]);
    }
}
