# teamspeak-cf-dns
 TeamSpeak CloudFlare DNS Service


    composer require mirarus/teamspeak-cf-dns

    -------------------------------------------------------

    require "vendor/autoload.php";

    use Mirarus\TeamSpeakCFDNS\Authorization;
    use Mirarus\TeamSpeakCFDNS\Dns;

    $authorization = new Authorization("--cloudflare--email--", "--cloudflare-apiKey--");
    $dns = new Dns($authorization);
    $dns->setDomain("mirarus.com.tr");

    var_dump($dns->create("mirarus", "127.0.0.1", 9987)); // status => true | false
    var_dump($dns->update("mirarus", "127.0.0.1", 9987)); // status => true | false
    var_dump($dns->delete("mirarus")); // status => true | false