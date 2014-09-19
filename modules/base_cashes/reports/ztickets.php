<?php
//    Pastèque Web back office, Users module
//
//    Copyright (C) 2013 Scil (http://scil.coop)
//
//    This file is part of Pastèque.
//
//    Pastèque is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    Pastèque is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with Pastèque.  If not, see <http://www.gnu.org/licenses/>.

namespace BaseCashes;

$db = \Pasteque\DB::get();

// Cash session request
$sqls[] = "SELECT "
        . "CASHREGISTERS.NAME, CLOSEDCASH.HOSTSEQUENCE, CLOSEDCASH.MONEY, "
        . "CLOSEDCASH.DATESTART, CLOSEDCASH.DATEEND, "
        . "CLOSEDCASH.OPENCASH, CLOSEDCASH.CLOSECASH, CLOSEDCASH.EXPECTEDCASH, "
        . "COUNT(DISTINCT(RECEIPTS.ID)) AS TICKETS, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS) AS SALES, "
        . "SUM(TICKETLINES.PRICE * TICKETLINES.UNITS * (1 + TAXES.RATE)) "
        . "AS SALESVAT "
        . "FROM CLOSEDCASH "
        . "LEFT JOIN CASHREGISTERS ON "
        . "CLOSEDCASH.CASHREGISTER_ID = CASHREGISTERS.ID "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TICKETLINES ON TICKETLINES.TICKET = RECEIPTS.ID "
        . "LEFT JOIN TAXES ON TICKETLINES.TAXID = TAXES.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start "
        . "AND CLOSEDCASH.DATESTART <= :stop "
        . "GROUP BY CLOSEDCASH.MONEY, CASHREGISTERS.NAME, HOSTSEQUENCE, "
        . "DATESTART, DATEEND, OPENCASH, CLOSECASH "
        . "ORDER BY CLOSEDCASH.DATESTART DESC";

// Payments request
$sqls[] = "SELECT "
        . "CLOSEDCASH.MONEY, PAYMENTS.PAYMENT AS __KEY__, "
        . "SUM(PAYMENTS.TOTAL) AS __VALUE__ "
        . "FROM "
        . "CLOSEDCASH "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN PAYMENTS ON PAYMENTS.RECEIPT = RECEIPTS.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start "
        . "AND CLOSEDCASH.DATESTART <= :stop "
        . "GROUP BY CLOSEDCASH.MONEY, __KEY__ "
        . "ORDER BY CLOSEDCASH.DATESTART DESC";

// Taxes request
$sqls[] = "SELECT "
        . "CLOSEDCASH.MONEY, TAXES.NAME as __KEY__, "
        . $db->concat($db->concat("SUM(TAXLINES.BASE)", "'/'"), "SUM(TAXLINES.AMOUNT)") . " AS __VALUE__ "
        . "FROM CLOSEDCASH "
        . "LEFT JOIN RECEIPTS ON RECEIPTS.MONEY = CLOSEDCASH.MONEY "
        . "LEFT JOIN TAXLINES ON TAXLINES.RECEIPT = RECEIPTS.ID "
        . "LEFT JOIN TAXES ON TAXLINES.TAXID = TAXES.ID "
        . "WHERE CLOSEDCASH.DATESTART > :start "
        . "AND CLOSEDCASH.DATESTART < :stop "
        . "GROUP BY CLOSEDCASH.MONEY, TAXES.NAME "
        . "ORDER BY CLOSEDCASH.DATESTART DESC";

$fields = array("NAME", "HOSTSEQUENCE", "DATESTART", "DATEEND", "OPENCASH",
        "CLOSECASH", "EXPECTEDCASH", "TICKETS", "SALES", "SALESVAT");
$mergeFields = array("MONEY");
$headers = array(
        \i18n("CashRegister.label"),
        \i18n("Session"),
        \i18n("Session.openDate"),
        \i18n("Session.closeDate"),
        \i18n("Session.openCash"),
        \i18n("Session.closeCash"),
        \i18n("Session.expectedCash"),
        \i18n("Tickets", PLUGIN_NAME),
        \i18n("Sales", PLUGIN_NAME),
        \i18n("Sales with VAT", PLUGIN_NAME)
);

$report = new \Pasteque\MergedReport(PLUGIN_NAME, "ztickets",
        \i18n("Z tickets", PLUGIN_NAME),
        $sqls, $headers, $fields, $mergeFields);

$report->addInput("start", \i18n("Start date"), \Pasteque\DB::DATE);
$report->setDefaultInput("start", time() - (time() % 86400) - 86400);
$report->addInput("stop", \i18n("Stop date"), \Pasteque\DB::DATE);
$report->setDefaultinput("stop", time());

function cashMatch($val, $values) {
    if ($val != $values['CLOSECASH']) {
        return "<span style=\"color:#b00;\">" . $val . "</span>";
    }
    return $val;
}

$report->addFilter("DATESTART", "\Pasteque\stdtimefstr");
$report->addFilter("DATESTART", "\i18nDatetime");
$report->addFilter("DATEEND", "\Pasteque\stdtimefstr");
$report->addFilter("DATEEND", "\i18nDatetime");
$report->addFilter("OPENCASH", "\i18nCurr");
$report->addFilter("CLOSECASH", "\i18nCurr");
$report->addFilter("EXPECTEDCASH", "\i18nCurr");
$report->addFilter("SALES", "\i18nCurr");
$report->addFilter("SALESVAT", "\i18nCurr");
$report->addMergedFilter(0, "\i18nCurr");
$report->addMergedHeaderFilter(0, "\i18n");
$report->addMergedFilter(1, "\BaseCashes\\vatI18nCurr");

function vatI18nCurr($input) {
    $amounts = explode("/", $input);
    return \i18nCurr($amounts[0]) . " / " . \i18nCurr($amounts[1]);
}

$report->setVisualFilter("EXPECTEDCASH", "\BaseCashes\cashMatch");

\Pasteque\register_report($report);
