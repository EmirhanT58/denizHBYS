<?php

?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<aside class="w-72 bg-gradient-to-b from-blue-900 to-blue-800 text-white h-screen p-6 fixed shadow-xl">
    <div class="flex flex-wrap items-center text-center gap-4 mb-10 ">
        <img src="../img/sb.png" alt="Doktor" class="w-12 h-12 m-auto">
        <h2 class="text-lg font-bold ml-[30px]">T.C. Sağlık Bakanlığı</h2>
        <h2 class="text-sm ml-[50px]">SBSGM - Hasta Paneli</h2>
    </div>

    <nav>
        <ul class="space-y-3">
             <li>
                <a href="hasta_panel.php" class="flex items-center gap-3 p-3 hover:bg-blue-700 rounded-lg transition-all">
                    <i class="fa-solid fa-chart-simple w-6 text-center"> </i>
                    Ana Sayfa
                </a>
            </li>
            <li>
                <a href="recetelerim.php" class="flex items-center gap-3 p-3 hover:bg-blue-700 rounded-lg transition-all">
                    <i class="fas fa-calendar-check w-6 text-center"></i>
                    reçetelerim
                </a>
            </li>
            <li>
                <a href="randevularim.php" class="flex items-center gap-3 p-3 hover:bg-blue-700 rounded-lg transition-all">
                    <i class="fas fa-users w-6 text-center"></i>
                    Randevularım
                </a>
            </li>
            <li>
                <a href="raporlarim.php" class="flex items-center gap-3 p-3 hover:bg-blue-700 rounded-lg transition-all">
                    <i class="fa-solid fa-plus w-6 text-center"></i>
                    Raporlarım
                </a>
            </li>
            <li class="absolute bottom-6 left-0 right-0 px-6">
                <a href="logout.php" class="flex items-center gap-3 p-3 bg-red-600 hover:bg-red-700 rounded-lg transition-all">
                    <i class="fas fa-sign-out-alt w-6 text-center"></i>
                    Çıkış Yap
                </a>
            </li>
        </ul>
    </nav>
</aside>
