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

class LocationsService extends AbstractService {

    protected static $dbTable = "LOCATIONS";
    protected static $dbIdField = "ID";
    protected static $fieldMapping = array(
            "ID" => "id",
            "NAME" => "label",
    );

    protected function build($row, $pdo = null) {
        return Location::__build($row["ID"], $row["NAME"]);
    }

    public function create($area) {
        $pdo = PDOBuilder::getPDO();
        $stmt = $pdo->prepare("INSERT INTO LOCATIONS "
                . "(ID, NAME) VALUES (:id, :label)");
        $id = md5(time() . rand());
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":label", $area->label);
        if ($stmt->execute() !== false) {
            return $id;
        } else {
            return false;
        }
    }

}