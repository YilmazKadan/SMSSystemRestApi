# SMS SYSTEM REST API

Bu proje verilen bir case'i çözmek amaçlı oluşturulmuştur.
İstenilen özellikler şunlardır:

- Sisteme login, register olma ve JWT token alma
- Sms gönderme (Sms gönderimi için sağlayıcı firmaya request
  atılıyor fakat siz bu kısmı es geçip sms gönderim sonrası
  giden sms detayını raporlara kaydetmelisiniz. Örnek fieldlar;
  number, message, send_time).
- Sms raporlarını görme ve tarihe göre filtreleme
- Sms rapor detayını görme


## Proje içinde kullanılan teknolojiler

- JWT token sistemi için **tymon/jwt-auth** paketi kullanılmıştır.
- API dokümantasyonu oluşturmak için **darkaonline/l5-swagger** paketi kullanılmıştır.

## Kurulum

Projeyi elde ettiken sonra ``composer
install`` komutu ile tüm bağımlılıkları yüklüyoruz.

``php artisan key:generate`` komutu ile uygulama anahtarı oluşturulmalıdır.
Bu anahtar session ve diğer şifrelenmiş verilerin güvenliğinde 
kullanılacaktır. key oluştuktan sonra .env dosyanız oluşacaktır. 
.env dosyanıza veritabanı ayarlamasını yapmanız gerekmekte.

### Yapılandırma 

``QUEUE_CONNECTION=database
``
Bu ayarlama kuyruk sistemi için gerekli.

Aşağıdaki komutu çalıştırmanız gerekmekte, **tymon/jwt-auth** paketi bizim
için arka planda kullanacağı güvenlik anahtarını oluşturacak
``php artisan jwt:generate``

Swagger Dokümantasyonunu tekrar oluşturmak için
``php artisan l5-swagger:generate`` komutunu çalıştırmanızda fayda var.


