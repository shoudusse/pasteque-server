<?php
//    POS-Tech API
//
//    Copyright (C) 2012 Scil (http://scil.coop)
//
//    This file is part of POS-Tech.
//
//    POS-Tech is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    POS-Tech is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with POS-Tech.  If not, see <http://www.gnu.org/licenses/>.

namespace Pasteque;

class ProductsService {

    private static function buildDBPrd($dpPrd, $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmt->execute(array(':id' => $dpPrd['ID']));
        $prdCat = $stmt->fetch();
        $visible = ($prdCat !== false);
        $dispOrder = null;
        if ($visible) {
            $dispOrder = $prdCat['CATORDER'];
        }
        return Product::__build($dpPrd['ID'], $dpPrd['REFERENCE'],
                $dpPrd['NAME'], $dpPrd['PRICESELL'], $dpPrd['CATEGORY'],
                $dispOrder, $dpPrd['TAXCAT'], $visible,
                ord($dpPrd['ISSCALE']) == 1, $dpPrd['PRICEBUY'],
                $dpPrd['ATTRIBUTESET_ID'], $dpPrd['CODE'],
                $dpPrd['IMAGE'] !== null,
                ord($dpPrd['DISCOUNTENABLED']) == 1, $dpPrd['DISCOUNTRATE']);
    }

    static function getAll($include_hidden = FALSE) {
        $prds = array();
        $pdo = PDOBuilder::getPDO();
        $sql = NULL;
        if ($include_hidden) {
            $sql = "SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT ON "
                    . "PRODUCTS_CAT.PRODUCT = PRODUCTS.ID "
                    . "WHERE DELETED = 0 ORDER BY CATORDER";
        } else {
            $sql = "SELECT * FROM PRODUCTS, PRODUCTS_CAT WHERE "
                    . "PRODUCTS.ID = PRODUCTS_CAT.PRODUCT AND DELETED = 0 "
                    . "ORDER BY CATORDER";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($dpPrd = $stmt->fetch()) {
            $prd = ProductsService::buildDBPrd($dpPrd, $pdo);
            $prds[] = $prd;
        }
        return $prds;
    }

    static function getPrepaidIds() {
        $ids = array();
        $pdo = PDOBuilder::getPDO();
        $sql = "SELECT ID FROM PRODUCTS WHERE CATEGORY = :cat AND DELETED = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":cat", '-1');
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $ids[] = $row['ID'];
        }
        return $ids;
    }

    static function getByRef($ref) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS "
            . "WHERE PRODUCTS.REFERENCE = :ref");
        $stmt->bindParam(":ref", $ref, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return null;
    }

    static function getByCode($code) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS "
            . "WHERE PRODUCTS.CODE = :code");
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return null;
    }

    static function getByCategory($categoryId) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS "
            . "WHERE PRODUCTS.CATEGORY = :cat");
        $stmt->bindParam(":cat", $categoryId, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            $prds = array();
            while ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                $prds[] = $prd;
            }
            return $prds;
        }
        return null;
    }

    static function get($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTS LEFT JOIN PRODUCTS_CAT "
                . "ON PRODUCTS_CAT.PRODUCT = PRODUCTS.ID WHERE ID = :id");
        if ($stmt->execute(array(':id' => $id))) {
            if ($row = $stmt->fetch()) {
                $prd = ProductsService::buildDBPrd($row, $pdo);
                return $prd;
            }
        }
        return null;
    }

    static function getImage($id) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("SELECT IMAGE FROM PRODUCTS WHERE ID = :id");
        $stmt->bindParam(":id", $id, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($row = $stmt->fetch()) {
                return $row['IMAGE'];
            }
        }
        return null;
    }

    /** Update a product. $prd->id must be set. Set $image to "" (default)
     * to keep the actual image */
    static function update($prd, $image = "") {
        $pdo = PDOBuilder::getPDO();
        $code = "";
        if ($prd->barcode != null) {
            $code = $prd->barcode;
        }
        $sql = "UPDATE PRODUCTS SET REFERENCE = :ref, CODE = :code, "
                . "NAME = :name, PRICEBUY = :buy, PRICESELL = :sell, "
                . "CATEGORY = :cat, TAXCAT = :tax, ATTRIBUTESET_ID = :attr, "
                . "ISSCALE = :scale, DISCOUNTENABLED = :discountEnabled, "
                . "DISCOUNTRATE = :discountRate";
        if ($image !== "") {
            $sql .= ", IMAGE = :img";
        }
        $sql .= " WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":ref", $prd->reference, \PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $prd->label, \PDO::PARAM_STR);
        if ($prd->priceBuy === null || $prd->priceBuy === "") {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_STR);
        }
        $stmt->bindParam(":sell", $prd->priceSell, \PDO::PARAM_STR);
        $stmt->bindParam(":cat", $prd->categoryId, \PDO::PARAM_INT);
        $stmt->bindParam(":tax", $prd->taxCatId, \PDO::PARAM_INT);
        $stmt->bindParam(":attr", $prd->attributeSetId, \PDO::PARAM_INT);
        $stmt->bindParam(":scale", $prd->scaled, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $prd->id, \PDO::PARAM_INT);
        $stmt->bindParam(":discountEnabled", $prd->discountEnabled,
                \PDO::PARAM_INT);
        if ($prd->discountRate === null || $prd->discountRate === "") {
            $stmt->bindValue(":discountRate", 0.0);
        } else {
            $stmt->bindParam(":discountRate", $prd->discountRate);
        }
        if ($image !== "") {
            $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        }
        $vsql = "DELETE FROM PRODUCTS_CAT WHERE PRODUCT = :id";
        $vstmt = $pdo->prepare($vsql);
        $vstmt->bindParam(":id", $prd->id, \PDO::PARAM_STR);
        $vstmt->execute();
        if ($prd->visible == 1 || $prd->visible == TRUE) {
            $vsql = "INSERT INTO PRODUCTS_CAT (PRODUCT, CATORDER) VALUES "
                    . "(:id, :dispOrder)";
            $vstmt = $pdo->prepare($vsql);
            $vstmt->bindParam(":id", $prd->id, \PDO::PARAM_STR);
            $vstmt->bindParam(":dispOrder", $prd->dispOrder, \PDO::PARAM_INT);
            $vstmt->execute();
        }
        if ($stmt->execute() !== false) {
            return true;
        } else {
            var_dump($stmt->errorInfo());
            return false;
        }
    }

    /** Create a product and return its id. */
    static function create($prd, $image = null) {
        $pdo = PDOBuilder::getPDO();
        $id = md5(time() . rand());
        $code = "";
        if ($prd->barcode != null) {
            $code = $prd->barcode;
        }
        $sql = "INSERT INTO PRODUCTS (ID, REFERENCE, CODE, NAME, "
                . "PRICEBUY, PRICESELL, CATEGORY, TAXCAT, "
                . "ATTRIBUTESET_ID, ISSCALE, DISCOUNTENABLED, DISCOUNTRATE";
        if ($prd->image !== "") {
            $sql .= ", IMAGE";
        }
        $sql .= ") VALUES (:id, :ref, :code, :name, :buy, :sell, :cat, "
                . ":tax, :attr, :scale, :discEnabled, :discRate";
        if ($prd->image !== "") {
            $sql .= ", :img";
        }
        $sql .= ")";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":ref", $prd->reference, \PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, \PDO::PARAM_STR);
        $stmt->bindParam(":name", $prd->label, \PDO::PARAM_STR);
        if ($prd->priceBuy === null || $prd->priceBuy === "") {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":buy", $prd->priceBuy, \PDO::PARAM_STR);
        }
        $stmt->bindParam(":sell", $prd->priceSell, \PDO::PARAM_STR);
        $stmt->bindParam(":cat", $prd->categoryId, \PDO::PARAM_INT);
        $stmt->bindParam(":tax", $prd->taxCatId, \PDO::PARAM_INT);
        $stmt->bindParam(":attr", $prd->attributeSetId, \PDO::PARAM_INT);
        $stmt->bindParam(":scale", $prd->scaled, \PDO::PARAM_INT);
        $stmt->bindParam(":discEnabled", $prd->discountEnabled,
                \PDO::PARAM_INT);
        if ($prd->discountRate === null || $prd->discountRate === "") {
            $stmt->bindValue(":discRate", 0.0);
        } else {
            $stmt->bindParam(":discRate", $prd->discountRate);
        }
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
        if ($image !== null) {
            $stmt->bindParam(":img", $image, \PDO::PARAM_LOB);
        }
        if (!$stmt->execute()) {
            return FALSE;
        }
        if ($prd->visible == 1 || $prd->visible == TRUE) {
            $catstmt = $pdo->prepare("INSERT INTO PRODUCTS_CAT (PRODUCT, "
                    . "CATORDER, POS_ID) "
                    . "VALUES (:id, :dispOrder, :pos)");
            $catstmt->bindParam(":id", $id);
            $catstmt->bindParam(":disp_order", $prd->dispOrder);
            $catstmt->bindValue(":pos", 1);
            $catstmt->execute();
        }
        return $id;
    }
    
    static function delete($id) {
        $pdo = PDOBuilder::getPDO();
        $stmtcat = $pdo->prepare("DELETE FROM PRODUCTS_CAT WHERE PRODUCT = :id");
        $stmtcat->execute(array(":id" => $id));
        $stmtstk = $pdo->prepare("DELETE FROM STOCKLEVEL WHERE PRODUCT = :id");
        $stmtstk->execute(array(":id" => $id));
        // Update reference with garbage to break unicity constraint
        $garbage = "_deleted_" . \md5(\time());
        $stmt = $pdo->prepare("UPDATE PRODUCTS SET DELETED = 1, "
               . "REFERENCE = concat(REFERENCE, :garbage), "
               . "NAME = concat(NAME, :garbage) WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':garbage', $garbage);
        return $stmt->execute();
    }
}

?>
