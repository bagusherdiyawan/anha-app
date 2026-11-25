<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RahasiaController extends Controller
{
    // 04+10=Empatbelas
    public function runSymlink(){
        $target = realpath($_SERVER['DOCUMENT_ROOT'] . "/../storage/app/public");
        $link = $_SERVER['DOCUMENT_ROOT'] . "/storage";

        echo "Target: " . $target . "<br>";
        echo "Link: " . $link . "<br>";

        if (!$target) {
            die("Error: Folder sumber (storage/app/public) tidak ditemukan di sistem!");
        }

        if (file_exists($link)) {
            echo "Link sudah ada, menghapus yang lama...<br>";
            if (is_link($link) || is_file($link)) {
                unlink($link); 
            } else {
        // Jika ternyata folder biasa, bukan link
                rmdir($link); 
            }
        }

        if (symlink($target, $link)) {
            echo "OK. Symlink berhasil dibuat.";
        } else {
            $lastError = error_get_last();
            echo "Gagal: " . $lastError['message'];
        }
    }
}
