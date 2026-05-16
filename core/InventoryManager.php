<?php
/**
 * INVENTORY MANAGER - Advanced Logic Engine
 */

class InventoryManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Register a new asset with CTI support.
     */
    public function register_new_asset($data, $user_id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Master Asset Table
            $sql = "INSERT INTO assets (asset_name, sku, category_id, category_type, tracking_type, current_stock, min_threshold, purchase_cost) 
                    VALUES (:name, :sku, :cat_id, :cat_type, :track, :stock, :threshold, :cost)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name'      => $data['asset_name'],
                ':sku'       => $data['sku'],
                ':cat_id'    => $data['category_id'],
                ':cat_type'  => $data['category_type'],
                ':track'     => $data['tracking_type'],
                ':stock'     => $data['current_stock'] ?? 0,
                ':threshold' => $data['min_threshold'] ?? 5,
                ':cost'      => $data['purchase_cost'] ?? 0
            ]);

            $asset_id = $this->pdo->lastInsertId();

            // 2. Sub-table Routing
            switch ($data['category_type']) {
                case 'operational':
                    $sql_sub = "INSERT INTO operational_assets (asset_id, serial_number, bore_caliber, license_number, last_calibration_date) VALUES (?, ?, ?, ?, ?)";
                    $params = [$asset_id, $data['serial_number'] ?? null, $data['bore_caliber'] ?? null, $data['license_number'] ?? null, $data['last_calibration_date'] ?? null];
                    break;
                case 'apparel':
                    $sql_sub = "INSERT INTO apparel_assets (asset_id, item_size, material_type) VALUES (?, ?, ?)";
                    $params = [$asset_id, $data['item_size'] ?? null, $data['material_type'] ?? null];
                    break;
                case 'logistics':
                    $sql_sub = "INSERT INTO logistics_assets (asset_id, registration_number, chassis_number, next_service_date) VALUES (?, ?, ?, ?)";
                    $params = [$asset_id, $data['registration_number'] ?? null, $data['chassis_number'] ?? null, $data['next_service_date'] ?? null];
                    break;
                case 'office':
                    $sql_sub = "INSERT INTO office_assets (asset_id, asset_type, location_room, depreciation_rate_annual) VALUES (?, ?, ?, ?)";
                    $params = [$asset_id, $data['asset_type_detail'] ?? null, $data['location_room'] ?? null, $data['depreciation_rate_annual'] ?? 0];
                    break;
                default:
                    throw new Exception("INVALID_CATEGORY_TYPE");
            }

            $this->pdo->prepare($sql_sub)->execute($params);

            // 3. Log initial transaction if stock > 0
            if (($data['current_stock'] ?? 0) > 0) {
                $this->log_transaction($asset_id, 'IN', $data['current_stock'], $data['purchase_cost'] ?? 0, $user_id, "Initial Stocking");
            }

            $this->pdo->commit();
            return ['status' => 'success', 'asset_id' => $asset_id];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Issues an asset to a guard.
     */
    public function issue_asset($asset_id, $guard_id, $qty, $user_id, $expected_return = null, $location = 'UNASSIGNED', $notes = '') {
        try {
            $this->pdo->beginTransaction();

            // Check stock
            $stmt = $this->pdo->prepare("SELECT current_stock FROM assets WHERE asset_id = ? FOR UPDATE");
            $stmt->execute([$asset_id]);
            $stock = $stmt->fetchColumn();

            if ($stock < $qty) throw new Exception("INSUFFICIENT_STOCK");

            // Deduct stock
            $this->pdo->prepare("UPDATE assets SET current_stock = current_stock - ? WHERE asset_id = ?")->execute([$qty, $asset_id]);

            // Log issuance
            $sql = "INSERT INTO asset_issuances (asset_id, guard_id, quantity, expected_return_date, return_remarks, status) 
                    VALUES (?, ?, ?, ?, ?, 'active_duty')";
            $this->pdo->prepare($sql)->execute([$asset_id, $guard_id, $qty, $expected_return, $notes]);

            // Log transaction
            $this->log_transaction($asset_id, 'OUT', $qty, 0, $user_id, "Issued to Guard ID: $guard_id");

            $this->pdo->commit();
            return ['status' => 'success'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Processes equipment returns and handles stock restoration.
     */
    public function process_return($issuance_id, $return_status, $remarks, $user_id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Get issuance details
            $stmt = $this->pdo->prepare("SELECT asset_id, quantity, status FROM asset_issuances WHERE issuance_id = ? FOR UPDATE");
            $stmt->execute([$issuance_id]);
            $issuance = $stmt->fetch();

            if (!$issuance) throw new Exception("ISSUANCE_NOT_FOUND");
            if ($issuance['status'] !== 'active_duty') throw new Exception("ALREADY_PROCESSED");

            // 2. Update Issuance record
            $sql_upd = "UPDATE asset_issuances SET status = ?, actual_return_date = CURRENT_TIMESTAMP, return_remarks = ? WHERE issuance_id = ?";
            $this->pdo->prepare($sql_upd)->execute([$return_status, $remarks, $issuance_id]);

            // 3. Restore stock if returned intact
            if ($return_status === 'returned_intact') {
                $this->pdo->prepare("UPDATE assets SET current_stock = current_stock + ? WHERE asset_id = ?")->execute([$issuance['quantity'], $issuance['asset_id']]);
                $this->log_transaction($issuance['asset_id'], 'IN', $issuance['quantity'], 0, $user_id, "Returned from Guard. Status: Intact.");
            } else {
                $this->log_transaction($issuance['asset_id'], 'OUT', 0, 0, $user_id, "Processed return. Status: $return_status. Qty {$issuance['quantity']} lost/damaged.");
            }

            $this->pdo->commit();
            return ['status' => 'success'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function log_transaction($asset_id, $type, $qty, $price, $user_id, $notes = '') {
        $sql = "INSERT INTO transactions (asset_id, trans_type, quantity, unit_price, performed_by, reference_notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->pdo->prepare($sql)->execute([$asset_id, $type, $qty, $price, $user_id, $notes]);
    }

    public function decommission_asset($asset_id) {
        $sql = "UPDATE assets SET is_deleted = 1 WHERE asset_id = ?";
        return $this->pdo->prepare($sql)->execute([$asset_id]);
    }

    public function get_inventory_list() {
        $sql = "SELECT a.*, c.category_name, o.serial_number 
                FROM assets a 
                JOIN categories c ON a.category_id = c.category_id 
                LEFT JOIN operational_assets o ON a.asset_id = o.asset_id 
                WHERE a.is_deleted = 0 
                ORDER BY a.asset_id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }
}
