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

class ImagesAPI extends APIService {

    protected function check() {
        switch ($this->action) {
        case 'getPrd':
        case 'getCat':
        case 'getPM':
            return isset($this->params['id']);
            break;
        case 'getRes':
            return isset($this->params['label']);
            break;
        }
        return false;
    }

    protected function proceed() {
        switch ($this->action) {
        case 'getPrd':
            $this->result = ProductsService::getImage($this->params['id']);
            break;
        case 'getCat':
            $this->result = CategoriesService::getImage($this->params['id']);
            break;
        case 'getPM':
            $srv = new PaymentModesService();
            $this->result = $srv->getImage($this->params['id']);
            break;
        case 'getRes':
            $this->result = ResourcesService::getImage($this->params['label']);
            break;
        }
    }

}
