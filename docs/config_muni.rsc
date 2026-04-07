# 2026-03-04 14:55:10 by RouterOS 7.21.3
# software id = D2WL-DLNT
#
# model = RB760iGS
# serial number = HE908QGFCAG
/interface bridge
add comment="el resto de puerto son lan" name=bridge1 port-cost-mode=short
/interface ethernet
set [ find default-name=ether1 ] comment="Starlink Internet" mac-address=\
    48:A9:8A:8B:6E:B9
set [ find default-name=ether2 ] comment="lan network" mac-address=\
    48:A9:8A:8B:6E:BA
set [ find default-name=ether3 ] mac-address=48:A9:8A:8B:6E:BB
set [ find default-name=ether4 ] mac-address=48:A9:8A:8B:6E:BC
set [ find default-name=ether5 ] mac-address=48:A9:8A:8B:6E:BD
set [ find default-name=sfp1 ] advertise="10M-baseT-half,10M-baseT-full,100M-b\
    aseT-half,100M-baseT-full,1G-baseT-half,1G-baseT-full" mac-address=\
    48:A9:8A:8B:6E:BE
/interface list
add name=WAN
add name=LAN
/interface lte apn
set [ find default=yes ] ip-type=ipv4 use-network-apn=no
/ip pool
add name=dhcp ranges=192.168.88.2-192.168.88.254
add name=PPT ranges=173.20.1.2-173.20.1.10
add name=vpn ranges=192.168.89.2-192.168.89.255
add name=dhcp_pool3 ranges=192.168.88.2-192.168.88.254
/ip dhcp-server
add address-pool=dhcp_pool3 interface=bridge1 lease-time=10m name=dhcp1
/ppp profile
add local-address=173.20.1.1 name=PPT remote-address=PPT
set *FFFFFFFE local-address=192.168.89.1 remote-address=vpn
/queue simple
add comment=ARQUITECTO max-limit=3M/4M name=AutoLimit-192.168.88.31 target=\
    192.168.88.31/32
add comment=JEFFRY max-limit=3M/4M name=AutoLimit-192.168.88.62 target=\
    192.168.88.62/32
add comment=JORDY max-limit=3M/4M name=AutoLimit-192.168.88.12 target=\
    192.168.88.12/32
add comment=SANDRA max-limit=3M/4M name=AutoLimit-192.168.88.42 target=\
    192.168.88.42/32
add comment=GERBER max-limit=50M/50M name=\
    "TECNICO EN INFORMATICA-192.168.88.19" target=192.168.88.10/32
add comment="Planificaci\F3n" max-limit=10M/20M name=\
    "Planificaci\F3n-192.168.88.92" target=192.168.88.54/32
add comment=AutoLimit max-limit=2M/2M name="CLINICA MUNICIPAL" target=\
    192.168.88.5/32
add comment="Impresora HP" disabled=yes max-limit=3M/4M name=\
    "Impresora HP t-192.168.88.7" target=192.168.88.7/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.252 target=\
    192.168.88.252/32
add comment="epson l4260" disabled=yes max-limit=3M/4M name=\
    "impresora epson-192.168.88.48" target=192.168.88.48/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.203 target=\
    192.168.88.203/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.168 target=\
    192.168.88.168/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.67 target=\
    192.168.88.67/32
add comment=EPSON2 disabled=yes max-limit=3M/4M name=\
    "IMPRESORA EPSON-192.168.88.173" target=192.168.88.173/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.226 target=\
    192.168.88.226/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.28 target=\
    192.168.88.28/32
add comment="IUSI Karina" max-limit=3M/4M name="IUSI Karinat-192.168.88.8" \
    target=192.168.88.8/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.94 target=\
    192.168.88.94/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.27 target=\
    192.168.88.27/32
add comment="CANON GX7000" disabled=yes max-limit=3M/4M name=\
    "CANON GX7000-192.168.88.18" target=192.168.88.18/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.6 target=\
    192.168.88.6/32
add comment=EPSON3 disabled=yes max-limit=3M/4M name=\
    "IMPRESORA EPSON3-192.168.88.11" target=192.168.88.11/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.65 target=\
    192.168.88.65/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.63 target=\
    192.168.88.63/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.66 target=\
    192.168.88.66/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.72 target=\
    192.168.88.72/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.36 target=\
    192.168.88.36/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.56 target=\
    192.168.88.56/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.13 target=\
    192.168.88.13/32
add comment="Discapacidad Armando" max-limit=3M/4M name=\
    Discapacidad_Armando-192.168.88.228 target=192.168.88.228/32
add comment=EPSON disabled=yes max-limit=3M/4M name=\
    "IMPRESORA EPSON-192.168.88.17" target=192.168.88.17/32
add comment="impresora epson l4260" disabled=yes max-limit=3M/4M name=\
    "epson l4260-192.168.88.2" target=192.168.88.2/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.229 target=\
    192.168.88.229/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.113 target=\
    192.168.88.113/32
add comment="Acceso Informaci\F3n P\FAblica Byron" max-limit=3M/4M name=\
    AccesoInfo_Byron-192.168.88.44 target=192.168.88.44/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.123 target=\
    192.168.88.123/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.30 target=\
    192.168.88.30/32
add comment="L4260 LIBRE ACCESO A LA INFORMACION" disabled=yes max-limit=\
    3M/4M name="IMPRESORA EPSON-192.168.88.24" target=192.168.88.24/32
add comment="IMPRESORA EPSON" disabled=yes max-limit=3M/4M name=\
    EPSON-192.168.88.29 target=192.168.88.29/32
add comment="SE\D1O WENDY RRHH" max-limit=5M/6M name=AutoLimit-192.168.88.33 \
    target=192.168.88.33/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.55 target=\
    192.168.88.55/32
add comment=TECNICO1 max-limit=100M/100M name=\
    "TECNICO CELULAR-192.168.88.231" target=192.168.88.231/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.78 target=\
    192.168.88.78/32
add comment="IMPRESORA CANON2" disabled=yes max-limit=3M/4M name=\
    "CANON 2-192.168.88.234" target=192.168.88.234/32
add comment=AutoLimit max-limit=3M/4M name=AutoLimit-192.168.88.198 target=\
    192.168.88.198/32
add comment="=====Lmite permanente GERSON======" max-limit=2M/3M name=\
    "Colaborador-Limitado Gerson" target=192.168.88.219/32
/queue tree
add comment="***===>QoS.2025.BLACK.USER.V3.0_By.Digicom<===***///////////Desca\
    rgas//////////" name=DESCARGAS parent=global
add max-limit=15M name=03_Streaming_Tv packet-mark=Streaming_Tv parent=\
    DESCARGAS priority=4
add max-limit=10M name=04_Youtube//Google packet-mark=Youtube//Google parent=\
    DESCARGAS priority=4
add name=05_Http&Https packet-mark=HTTP&HTTPS parent=DESCARGAS priority=5
add max-limit=10M name=02_RedesSociales packet-mark=Redes_Sociales parent=\
    DESCARGAS priority=3
add name=00_Dns.&.Icmp packet-mark=ICMP&DNS parent=DESCARGAS priority=1
add name=07_Games_On_Line packet-mark=GamesOnLine parent=DESCARGAS priority=7
add name=01_Clases_Virtuales packet-mark=CLASESVIRTUALES parent=DESCARGAS \
    priority=2
add name=06_Optimizacion packet-mark=OPTIMIZACION parent=DESCARGAS priority=6
add name=08_Resto packet-mark=RESTO parent=DESCARGAS
/queue type
add cake-diffserv=besteffort cake-flowmode=dual-dsthost cake-nat=yes kind=\
    cake name=Cake_Up
add cake-diffserv=besteffort cake-flowmode=dual-srchost cake-nat=yes kind=\
    cake name=Cake_Down
/queue tree
add comment="***===>QoS.2025.BLACK.USER.V3.0_By.Digicom<===***///////////Subid\
    as//////////" name=SUBIDAS parent=ether1 queue=default
add name=000_ICMP_&_DNS packet-mark=ICMP&DNS parent=SUBIDAS priority=1
add name=001_Clases_Virtuales packet-mark=CLASESVIRTUALES parent=SUBIDAS \
    priority=2
add max-limit=5M name=002_RedesSociales packet-mark=Redes_Sociales parent=\
    SUBIDAS priority=3
add max-limit=5M name=003_Streaming_Tv packet-mark=Streaming_Tv parent=\
    SUBIDAS priority=4
add max-limit=5M name=004_Youtube//Google packet-mark=Youtube//Google parent=\
    SUBIDAS priority=4
add name=005_Http&Https packet-mark=HTTP&HTTPS parent=SUBIDAS priority=5
add name=007_Games_On_Line packet-mark=GamesOnLine parent=SUBIDAS priority=7
add name=006_Optimizacion packet-mark=OPTIMIZACION parent=SUBIDAS priority=6
add name=008_Resto packet-mark=RESTO parent=SUBIDAS
/interface bridge port
add bridge=bridge1 ingress-filtering=no interface=ether2 internal-path-cost=\
    10 path-cost=10
add bridge=bridge1 ingress-filtering=no interface=ether3 internal-path-cost=\
    10 path-cost=10
add bridge=bridge1 ingress-filtering=no interface=ether4 internal-path-cost=\
    10 path-cost=10
add bridge=bridge1 ingress-filtering=no interface=ether5 internal-path-cost=\
    10 path-cost=10
add bridge=bridge1 ingress-filtering=no interface=sfp1 internal-path-cost=10 \
    path-cost=10
/ip firewall connection tracking
set udp-timeout=10s
/ipv6 settings
set disable-ipv6=yes max-neighbor-entries=8192
/interface l2tp-server server
set enabled=yes use-ipsec=yes
/interface list member
add interface=ether1 list=WAN
add interface=bridge1 list=LAN
/interface ovpn-server server
add auth=sha1,md5 mac-address=FE:2C:CB:FA:B4:3B name=ovpn-server1
/interface pptp-server server
# PPTP connections are considered unsafe, it is suggested to use a more modern VPN protocol instead
set authentication=pap,chap,mschap1,mschap2 enabled=yes
/ip address
add address=192.168.88.1/24 comment=LAN interface=ether2 network=192.168.88.0
/ip arp
add address=179.60.175.196 disabled=yes interface=ether1
add address=192.168.88.57 disabled=yes interface=bridge1 mac-address=\
    3E:83:95:DD:7B:CB
add address=192.168.88.3 disabled=yes interface=bridge1 mac-address=\
    3E:FD:F3:6B:6C:BD
add address=192.168.88.6 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:0D:80:7B
add address=192.168.88.7 disabled=yes interface=bridge1 mac-address=\
    84:2A:FD:FF:7B:DE
add address=192.168.88.4 disabled=yes interface=bridge1 mac-address=\
    0E:F2:1F:33:CE:1D
add address=192.168.88.10 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:0D:82:30
add address=192.168.88.11 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:0D:7E:AB
add address=192.168.88.17 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:08:DC:19
add address=192.168.88.24 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:1C:FE:73
add address=192.168.88.29 disabled=yes interface=bridge1 mac-address=\
    E2:4D:C5:70:27:DB
add address=192.168.88.42 disabled=yes interface=bridge1 mac-address=\
    A4:D7:3C:20:71:A5
add address=192.168.88.107 disabled=yes interface=bridge1 mac-address=\
    38:DE:AD:92:11:34
add address=192.168.88.126 comment="ARMANDO OF. DISCAPACIDAD" disabled=yes \
    interface=bridge1 mac-address=F0:03:8C:64:5B:3D
add address=192.168.88.14 disabled=yes interface=bridge1 mac-address=\
    16:60:74:3D:37:CA
add address=192.168.88.12 disabled=yes interface=bridge1 mac-address=\
    5C:3A:45:83:62:77
add address=192.168.88.25 disabled=yes interface=bridge1 mac-address=\
    10:27:F5:55:A0:91
add address=192.168.88.30 disabled=yes interface=bridge1 mac-address=\
    10:27:F5:55:AB:EC
add address=192.168.88.31 comment=ARQUITECTO disabled=yes interface=bridge1 \
    mac-address=E8:FB:1C:C0:2C:F7
add address=192.168.88.35 disabled=yes interface=bridge1 mac-address=\
    10:27:F5:55:A0:91
add address=192.168.88.43 disabled=yes interface=bridge1 mac-address=\
    CC:47:40:72:5F:C5
add address=192.168.88.47 disabled=yes interface=bridge1 mac-address=\
    10:27:F5:55:9B:F5
add address=192.168.88.49 disabled=yes interface=bridge1 mac-address=\
    2E:A5:14:69:C4:76
add address=192.168.88.50 disabled=yes interface=bridge1 mac-address=\
    A0:D7:68:30:0A:C5
add address=192.168.88.51 disabled=yes interface=bridge1 mac-address=\
    18:4E:CB:1B:1A:9B
add address=192.168.88.64 disabled=yes interface=bridge1 mac-address=\
    F0:77:C3:FF:B2:84
add address=192.168.88.84 disabled=yes interface=bridge1 mac-address=\
    78:46:5C:3B:FD:3B
add address=192.168.88.140 disabled=yes interface=bridge1 mac-address=\
    B8:1E:A4:A0:B3:E3
add address=192.168.88.154 disabled=yes interface=bridge1 mac-address=\
    BE:88:DD:E5:EC:B2
add address=192.168.88.186 disabled=yes interface=bridge1 mac-address=\
    8E:4A:86:5B:BE:AC
add address=192.168.88.188 disabled=yes interface=bridge1 mac-address=\
    CC:47:40:72:1E:ED
add address=192.168.88.195 disabled=yes interface=bridge1 mac-address=\
    DE:7A:CB:37:43:95
add address=192.168.88.20 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:1C:FE:73
add address=192.168.88.36 disabled=yes interface=bridge1 mac-address=\
    E0:BB:9E:1C:FE:8B
add address=192.168.88.37 disabled=yes interface=bridge1 mac-address=\
    96:15:6B:99:60:84
add address=192.168.88.83 disabled=yes interface=bridge1 mac-address=\
    5E:01:62:CB:52:9D
add address=192.168.88.97 disabled=yes interface=bridge1 mac-address=\
    DE:15:E5:DD:7A:5E
add address=192.168.88.99 disabled=yes interface=bridge1 mac-address=\
    B6:7C:35:F4:37:12
add address=192.168.88.101 disabled=yes interface=bridge1 mac-address=\
    DC:CD:2F:A9:9F:8F
add address=192.168.88.102 disabled=yes interface=bridge1 mac-address=\
    A4:D7:3C:3F:52:B7
add address=192.168.88.253 disabled=yes interface=bridge1 mac-address=\
    A4:D7:3C:20:71:A5
add address=192.168.88.111 disabled=yes interface=bridge1 mac-address=\
    92:B2:E0:D3:DE:CC
add address=192.168.88.172 disabled=yes interface=bridge1 mac-address=\
    42:26:E8:B7:8F:BF
add address=192.168.88.57 disabled=yes interface=bridge1 mac-address=\
    3A:EC:27:24:A8:E7
add address=192.168.88.147 disabled=yes interface=bridge1 mac-address=\
    82:B7:D5:77:A9:AB
add address=192.168.88.231 interface=bridge1 mac-address=96:EE:3B:7B:66:5C
add address=192.168.88.125 disabled=yes interface=bridge1 mac-address=\
    38:DE:AD:92:11:34
add address=192.168.88.10 interface=bridge1
add address=192.168.88.224 interface=bridge1 mac-address=10:27:F5:55:AB:AF
/ip cloud
set ddns-enabled=yes
/ip dhcp-client
add interface=ether1
/ip dhcp-server lease
add address=192.168.88.5 client-id=1:80:69:1a:66:59:8b mac-address=\
    80:69:1A:66:59:8B server=dhcp1
add address=192.168.88.7 client-id=1:84:2a:fd:ff:7b:de mac-address=\
    84:2A:FD:FF:7B:DE server=dhcp1
add address=192.168.88.16 client-id=1:b6:d:2b:8f:96:e mac-address=\
    B6:0D:2B:8F:96:0E server=dhcp1
add address=192.168.88.53 client-id=1:f2:29:23:12:4c:b0 mac-address=\
    F2:29:23:12:4C:B0 server=dhcp1
add address=192.168.88.115 client-id=1:a0:d7:68:10:a:9b comment=TECNICO \
    mac-address=A0:D7:68:10:0A:9B server=dhcp1
add address=192.168.88.219 comment=Colaborador-Fijo mac-address=\
    9C:B7:0D:19:89:0B server=dhcp1
/ip dhcp-server network
add address=192.168.88.0/24 dns-server=192.168.88.1 gateway=192.168.88.1 \
    netmask=24
/ip dns
set allow-remote-requests=yes servers=8.8.8.8,8.8.4.4
/ip firewall address-list
add address=www.whatsapp.com list=Whatsapp
add address=www.web-whatsapp.com list=Whatsapp
add address=i.instagram.com list=Instagram
add address=graph.instagram.com comment=Instagram list=Instagram
add address=g.whatsapp.net list=Whatsapp
add address=chat.cdn.whatsapp.net list=Whatsapp
add address=www.instagram.com list=Instagram
add address=52.3.144.142 list=netflix
add address=skype.com list=Skype
add address=secure.skype.com list=Skype
add address=login.skype.com list=Skype
add address=zoom.us list=Zoom
add address=zoom.com list=Zoom
add address=classroom.google.com list=Classroom
add address=meet.google.com list=Meet
add address=mail.google.com list=Gmail
add address=instagram.com list=Instagram
add address=cdninstagram.com list=Instagram
add address=www.cdninstagram.com list=Instagram
add address=api.instagram.com list=instagram
add address=scontent.cdninstagram.com list=Instagram
add address=help.instagram.com list=Instagram
add address=business.instagram.com list=Instagram
add address=creators.instagram.com list=Instagram
add address=shopping.instagram.com list=Instagram
add address=static.cdninstagram.com list=Instagram
add address=ads.instagram.com list=Instagram
add address=media.cdninstagram.com list=Instagram
add address=ig.me list=Instagram
add address=fbcdn-ig.net list=Instagram
add address=scontent.xx.fbcdn.net list=Instagram
add address=31.13.24.0/21 list=Facebook
add address=31.13.64.0/18 list=Facebook
add address=66.220.144.0/20 list=Facebook
add address=69.63.176.0/20 list=Facebook
add address=69.171.224.0/19 list=Facebook
add address=69.171.240.0/20 list=Facebook
add address=157.240.0.0/17 list=Facebook
add address=173.252.64.0/19 list=Facebook
add address=185.60.216.0/22 list=Facebook
add address=facebook.com list=Facebook
add address=www.facebook.com list=Facebook
add address=m.facebook.com list=Facebook
add address=fbcdn.net list=Facebook
add address=staticxx.facebook.com list=Facebook
add address=connect.facebook.net list=Facebook
add address=messenger.com list=Facebook
add address=www.messenger.com list=Facebook
add address=fb.me list=Facebook
add address=fb.com list=Facebook
add address=video.xx.fbcdn.net list=Facebook
add address=scontent.xx.fbcdn.net list=Facebook
add address=scontent.fcuz1-1.fna.fbcdn.net list=Facebook
add address=edge-chat.facebook.com list=Facebook
add address=graph.facebook.com list=Facebook
add address=developers.facebook.com list=Facebook
add address=business.facebook.com list=Facebook
add address=twitter.com list=Twitter
add address=www.twitter.com list=Twitter
add address=api.twitter.com list=Twitter
add address=mobile.twitter.com list=Twitter
add address=ads.twitter.com list=Twitter
add address=analytics.twitter.com list=Twitter
add address=pbs.twimg.com list=Twitter
add address=video.twimg.com list=Twitter
add address=ton.twitter.com list=Twitter
add address=help.twitter.com list=Twitter
add address=abs.twimg.com list=Twitter
add address=o.twimg.com list=Twitter
add address=developer.twitter.com list=Twitter
add address=cdn.twitter.com list=Twitter
add address=tweetdeck.twitter.com list=Twitter
add address=t.co list=Twitter
add address=x.com list=Twitter
add address=www.x.com list=Twitter
add address=api.x.com list=Twitter
add address=ads.x.com list=Twitter
add address=tiktok.com list=TikTok
add address=www.tiktok.com list=TikTok
add address=m.tiktok.com list=TikTok
add address=api.tiktok.com list=TikTok
add address=ads.tiktok.com list=TikTok
add address=log.tiktokv.com list=TikTok
add address=p16-tiktokcdn-com.akamaized.net list=TikTok
add address=mon.tiktokglobalapps.com list=TikTok
add address=tiktokv.com list=TikTok
add address=sf16-scmcdn-tos.pstatp.com list=TikTok
add address=lf16-tiktokcdn-tos.pstatp.com list=TikTok
add address=ib.tiktokv.com list=TikTok
add address=ns.tiktokv.com list=TikTok
add address=bytedance.net list=TikTok
add address=www.bdxcdn.com list=TikTok
add address=bdxcdn.com list=TikTok
add address=abtest-va.tiktokv.com list=TikTok
add address=sec-tiktokcdn-va.akamaized.net list=TikTok
add address=v16m.tiktokcdn.com list=TikTok
add address=whatsapp.com list=Whatsapp
add address=web.whatsapp.com list=Whatsapp
add address=api.whatsapp.com list=Whatsapp
add address=mmg.whatsapp.net list=Whatsapp
add address=media.whatsapp.net list=Whatsapp
add address=cdn.whatsapp.net list=Whatsapp
add address=chat.whatsapp.com list=Whatsapp
add address=call.whatsapp.com list=Whatsapp
add address=w1.whatsapp.net list=Whatsapp
add address=w2.whatsapp.net list=Whatsapp
add address=w3.whatsapp.net list=Whatsapp
add address=static.whatsapp.net list=Whatsapp
add address=st.whatsapp.net list=Whatsapp
add address=video-cdn.whatsapp.net list=Whatsapp
add address=netflix.com list=Netflix
add address=www.netflix.com list=Netflix
add address=api.netflix.com list=Netflix
add address=watch.netflix.com list=Netflix
add address=cdn2.netflix.com list=Netflix
add address=video-s3.netflix.com list=Netflix
add address=static.netflix.com list=Netflix
add address=preprod.netflix.com list=Netflix
add address=secure.netflix.com list=Netflix
add address=netflix.net list=Netflix
add address=nflximg.net list=Netflix
add address=nflxso.net list=Netflix
add address=nflxvideo.net list=Netflix
add address=flicks.com list=Netflix
add address=streaming.netflix.com list=Netflix
add address=e.netflix.com list=Netflix
add address=ns.netflix.com list=Netflix
add address=amazon.com list=Amazon
add address=www.amazon.com list=Amazon
add address=amazonaws.com list=Amazon
add address=s3.amazonaws.com list=Amazon
add address=primevideo.com list=Amazon
add address=www.primevideo.com list=Amazon
add address=video.amazon.com list=Amazon
add address=www.amazonvideo.com list=Amazon
add address=dp.amazon.com list=Amazon
add address=dpd.amazon.com list=Amazon
add address=images.amazon.com list=Amazon
add address=static.amazon.com list=Amazon
add address=aws.amazon.com list=Amazon
add address=www.aws.amazon.com list=Amazon
add address=cloudfront.net list=Amazon
add address=awsstatic.com list=Amazon
add address=alexa.amazon.com list=Amazon
add address=www.alexa.com list=Amazon
add address=amazon.co.uk list=Amazon
add address=www.amazon.co.uk list=Amazon
add address=disneyplus.com list=DisneyPlus
add address=www.disneyplus.com list=DisneyPlus
add address=media.disneyplus.com list=DisneyPlus
add address=cdn.disneyplus.com list=DisneyPlus
add address=static.disneyplus.com list=DisneyPlus
add address=disneyplusdn.com list=DisneyPlus
add address=disneyplus.global list=DisneyPlus
add address=disneyplus.co.uk list=DisneyPlus
add address=www.disneyplus.co.uk list=DisneyPlus
add address=hbo.com list=HBO
add address=www.hbo.com list=HBO
add address=hboasia.com list=HBO
add address=hboeu.com list=HBO
add address=hbo.com.br list=HBO
add address=hbomax.com list=HBO
add address=www.hbomax.com list=HBO
add address=play.hbomax.com list=HBO
add address=watch.hbomax.com list=HBO
add address=cdn.hbomax.com list=HBO
add address=static.hbomax.com list=HBO
add address=hbo.lat list=HBO
add address=hbomaxlatam.com list=HBO
add address=max.com list=HBO
add address=play.max.com list=HBO
add address=youtube.com list=Youtube
add address=googlevideo.com list=Youtube
add address=youtu.be list=Youtube
add address=akamaihd.net list=Youtube
add address=google.com list=Google
add address=www.google.com list=Google
add address=accounts.google.com list=Google
add address=drive.google.com list=Google
add address=docs.google.com list=Google
add address=maps.google.com list=Google
add address=photos.google.com list=Google
add address=play.google.com list=Google
add address=translate.google.com list=Google
add address=plus.google.com list=Google
add address=calendar.google.com list=Google
add address=sites.google.com list=Google
add address=video.google.com list=Google
add address=books.google.com list=Google
add address=scholar.google.com list=Google
add address=news.google.com list=Google
add address=shopping.google.com list=Google
add address=216.239.32.0/19 list=Google
add address=64.68.80.0/21 list=Google
add address=66.102.0.0/20 list=Google
add address=108.177.0.0/17 list=Google
add address=64.68.88.0/21 list=Google
add address=199.36.152.0/21 list=Google
add address=142.250.0.0/15 list=Google
add address=216.58.192.0/19 list=Google
add address=172.217.0.0/16 list=Google
add address=74.114.24.0/21 list=Google
add address=208.81.188.0/22 list=Google
add address=108.170.192.0/18 list=Google
add address=172.253.0.0/16 list=Google
add address=173.194.0.0/16 list=Google
add address=192.178.0.0/15 list=Google
add address=207.223.160.0/20 list=Google
add address=209.85.128.0/17 list=Google
add address=64.233.160.0/19 list=Google
add address=66.249.64.0/19 list=Google
add address=70.32.128.0/19 list=Google
add address=72.14.192.0/18 list=Google
add address=74.125.0.0/16 list=Google
add address=208.68.108.0/22 list=Google
add address=64.15.112.0/20 list=Google
add address=192.168.88.32 comment=PLANIFICACIONCHECHITA list=NoLimit
add address=192.168.88.125 comment=tecc list=NoLimit
add address=192.168.88.15 comment=UAP-1 list=NoLimit
add address=192.168.88.23 comment=UAP-2 list=NoLimit
add address=192.168.88.246 comment=UAP-3 list=NoLimit
add address=192.168.88.245 comment=UAP-4 list=NoLimit
add address=192.168.88.39 comment=UAP-5 list=NoLimit
add address=192.168.88.248 comment=UAP-6 list=NoLimit
/ip firewall filter
add action=accept chain=input protocol=icmp
add action=accept chain=input comment="allow IPsec NAT" dst-port=4500 \
    protocol=udp
add action=accept chain=input comment="allow IKE" dst-port=500 protocol=udp
add action=accept chain=input comment="allow l2tp" dst-port=1701 protocol=udp
add action=drop chain=input comment="Bloqueo webproxy externo" dst-port=8080 \
    in-interface=ether1 protocol=tcp
add action=drop chain=input comment="Bloqueo DNS cache externo" dst-port=53 \
    in-interface=ether1 protocol=udp
add action=drop chain=input comment=\
    Evitar_ataque_de_ping_al_servidor_MikroTik in-interface=ether1 protocol=\
    icmp
add action=drop chain=input comment="Proteccion VSC contra ataques via SSH" \
    dst-port=22 protocol=tcp src-address-list=ssh_blacklist
add action=add-src-to-address-list address-list=ssh_blacklist \
    address-list-timeout=1w3d chain=input connection-state=new dst-port=22 \
    protocol=tcp src-address-list=ssh_stage3
add action=add-src-to-address-list address-list=ssh_stage2 \
    address-list-timeout=1m chain=input connection-state=new dst-port=22 \
    protocol=tcp src-address-list=ssh_stage1
add action=add-src-to-address-list address-list=ssh_stage1 \
    address-list-timeout=1m chain=input connection-state=new dst-port=22 \
    protocol=tcp
add action=add-src-to-address-list address-list=ssh_stage3 \
    address-list-timeout=1m chain=input connection-state=new dst-port=22 \
    protocol=tcp src-address-list=ssh_stage2
add action=accept chain=input comment="Firewall WAN" dst-port=8291,8000,1723 \
    in-interface=ether1 protocol=tcp
add chain=input connection-state=established in-interface=ether1
add chain=input connection-state=related in-interface=ether1
add action=drop chain=input in-interface=ether1
add action=accept chain=input connection-limit=100,32 src-address=\
    192.168.146.154
add action=drop chain=forward comment="Bloqueo QUIC para forzar HTTP/HTTPS" \
    port=443 protocol=udp
/ip firewall mangle
add action=mark-connection chain=forward comment="***===>QoS.2025.BLACK.USER.V\
    3.0_By.Digicom<===***///////////ICMP & DNS//////////" \
    new-connection-mark=ICMP&DNS-conn protocol=icmp
add action=mark-connection chain=forward dst-port=53 new-connection-mark=\
    ICMP&DNS-conn protocol=udp
add action=mark-connection chain=forward dst-port=53 new-connection-mark=\
    ICMP&DNS-conn protocol=tcp
add action=mark-packet chain=forward connection-mark=ICMP&DNS-conn \
    new-packet-mark=ICMP&DNS passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--STREAMING--:::::::::::::::://////*****===>" new-connection-mark=\
    Streaming-tls src-address-list=Netflix
add action=mark-connection chain=forward new-connection-mark=Streaming-tls \
    src-address-list=Amazon
add action=mark-connection chain=forward new-connection-mark=Streaming-tls \
    src-address-list=DisneyPlus
add action=mark-connection chain=forward new-connection-mark=Streaming-tls \
    src-address-list=HBO
add action=add-dst-to-address-list address-list=Streaming \
    address-list-timeout=2h chain=forward comment=\
    "add::Streaming_Tv::: Address" connection-mark=Streaming-tls
add action=mark-connection chain=forward comment=STREAMING_TV \
    dst-address-list=Streaming new-connection-mark=Streaming-add protocol=tcp
add action=mark-packet chain=forward connection-mark=Streaming-add \
    new-packet-mark=Streaming_Tv passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--AULAS.GOOGLE--:::::::::::::::://////*****===>" dst-address-list=\
    Classroom new-connection-mark=Clases-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=Clases-add \
    src-address-list=Classroom
add action=mark-connection chain=forward dst-address-list=Meet \
    new-connection-mark=Clases-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=Clases-add \
    src-address-list=Meet
add action=mark-connection chain=forward dst-address-list=Gmail \
    new-connection-mark=Clases-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=Clases-add \
    src-address-list=Gmail
add action=mark-packet chain=forward connection-mark=Clases-add \
    new-packet-mark=CLASESVIRTUALES passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--REDES.SOCIALES--:::::::::::::::://////*****===>" dst-address-list=\
    Facebook new-connection-mark=RedesSociales-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=\
    RedesSociales-add src-address-list=Facebook
add action=mark-connection chain=forward content=facebook.com \
    new-connection-mark=RedesSociales-add
add action=mark-connection chain=forward dst-address-list=Instagram \
    new-connection-mark=RedesSociales-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=\
    RedesSociales-add src-address-list=Instagram
add action=mark-connection chain=forward content=instagram.com \
    new-connection-mark=RedesSociales-add
add action=mark-connection chain=forward dst-address-list=Twitter \
    new-connection-mark=RedesSociales-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=\
    RedesSociales-add src-address-list=Twitter
add action=mark-connection chain=forward content=x.com new-connection-mark=\
    RedesSociales-add
add action=mark-connection chain=forward dst-address-list=TikTok \
    new-connection-mark=RedesSociales-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=\
    RedesSociales-add src-address-list=TikTok
add action=mark-connection chain=forward content=tiktok.com \
    new-connection-mark=RedesSociales-add
add action=mark-connection chain=forward dst-address-list=Whatsapp \
    new-connection-mark=RedesSociales-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=\
    RedesSociales-add src-address-list=Whatsapp
add action=mark-connection chain=forward new-connection-mark=\
    RedesSociales-add port=3478 protocol=udp
add action=mark-connection chain=forward dst-port=\
    5222,5223,5228,4244,5242,50318,59234 new-connection-mark=\
    RedesSociales-add protocol=tcp
add action=mark-packet chain=forward connection-mark=RedesSociales-add \
    new-packet-mark=Redes_Sociales passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--YOUTUBE::GOOGLE::Streaming--:::::::::::::::://////*****===>" content=\
    youtube.com new-connection-mark=Youtube-add protocol=tcp
add action=mark-connection chain=forward comment=Quic.k dst-port=443,80 \
    new-connection-mark=Youtube-add protocol=udp
add action=mark-connection chain=forward dst-address-list=Youtube \
    new-connection-mark=Youtube-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=Youtube-add \
    src-address-list=Youtube
add action=mark-connection chain=forward dst-address-list=Google \
    new-connection-mark=Youtube-add protocol=tcp
add action=mark-connection chain=forward new-connection-mark=Youtube-add \
    src-address-list=Google
add action=mark-packet chain=forward connection-mark=Youtube-add \
    new-packet-mark=Youtube//Google passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--OPTIMIZACION - RCG - WOW--:::::::::::::::://////*****===>" \
    new-connection-mark=optimizacion-add protocol=tcp src-port=\
    6000-6111,6120-6880,18600
add action=mark-connection chain=forward new-connection-mark=optimizacion-add \
    protocol=tcp src-port=8085-8300,9643-9700,47200-47300
add action=mark-packet chain=forward connection-mark=optimizacion-add \
    new-packet-mark=OPTIMIZACION passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--GAMES ON LINE--:::::::::::::::://////*****===>" dst-port=\
    10000-11008,7008 new-connection-mark=GameOnLine-add protocol=udp
add action=mark-connection chain=forward dst-port=9000-9099 \
    new-connection-mark=GameOnLine-add protocol=udp
add action=mark-connection chain=forward dst-port=\
    7700,1900,17000,65050,7500,65010,8700,3013,7703,7520,7535,7752 \
    new-connection-mark=GameOnLine-add protocol=udp
add action=mark-connection chain=forward dst-port=\
    20000-20099,12235,13748,13972,13894,11455,7000-7011 new-connection-mark=\
    GameOnLine-add protocol=udp
add action=mark-connection chain=forward dst-port=\
    88,500,3074,3544,4500,3075,4379-4380,27000-27031,27036,7542,7608 \
    new-connection-mark=GameOnLine-add protocol=udp
add action=mark-connection chain=forward dst-port=\
    14009-14030,42051-42052,40000-40050,13000-13080 new-connection-mark=\
    GameOnLine-add protocol=udp
add action=mark-connection chain=forward dst-port=\
    39190,27780,29000,22100,4300,15001,15002,7341,7451 new-connection-mark=\
    GameOnLine-add protocol=tcp
add action=mark-connection chain=forward dst-port=\
    39003,39698,39779,10001,10003,10012,10001,10003,10012 \
    new-connection-mark=GameOnLine-add protocol=tcp
add action=mark-connection chain=forward dst-port=\
    5340-5352,6000-6152,14009-14030,18901-18909 new-connection-mark=\
    GameOnLine-add protocol=tcp
add action=mark-connection chain=forward dst-port=\
    40000,9300,9400,9700,7342,8005-8010,37466,36567,8822 new-connection-mark=\
    GameOnLine-add protocol=tcp
add action=mark-connection chain=forward comment=:::Free.Fire::: dst-port=\
    39003,39698,39779,10001,10003,10012,10001,10003,10012 \
    new-connection-mark=GameOnLine-add protocol=udp
add action=mark-connection chain=forward comment="::: Dota.2:::" dst-port=\
    27000-28998,27000-28998 new-connection-mark=GameOnLine-add protocol=udp \
    src-port=""
add action=mark-connection chain=forward comment="::: Fifa.Online:::" \
    dst-port=7770-7790,16300-16350 new-connection-mark=GameOnLine-add \
    protocol=udp src-port=""
add action=mark-connection chain=forward comment="::: Clash.Royale-Cry:::" \
    dst-port=9330-9340,9330-9340 new-connection-mark=GameOnLine-add protocol=\
    udp src-port=""
add action=mark-packet chain=forward connection-mark=GameOnLine-add \
    new-packet-mark=GamesOnLine passthrough=no
add action=mark-connection chain=forward comment="*****///////::::::::::::::::\
    :--HTTP.&.HTTPS--:::::::::::::::://////*****===>" dst-port=80,443 \
    new-connection-mark=Http&Https-add protocol=tcp
add action=mark-packet chain=forward connection-mark=Http&Https-add \
    new-packet-mark=HTTP&HTTPS passthrough=no
add action=mark-connection chain=forward comment=\
    "*****///////:::::::::::::::::--RESTO--:::::::::::::::://////*****===>" \
    connection-state=new new-connection-mark=Resto-add
add action=mark-connection chain=forward new-connection-mark=Resto-add \
    packet-mark=no-mark
add action=mark-packet chain=forward connection-mark=Resto-add \
    new-packet-mark=RESTO passthrough=no
/ip firewall nat
add action=masquerade chain=srcnat comment="CONEXION DE INTERNET" \
    out-interface=ether1
/ip ipsec profile
set [ find default=yes ] dpd-interval=2m dpd-maximum-failures=5
/ip proxy
set enabled=yes
/ip route
add check-gateway=ping distance=1 dst-address=0.0.0.0/0 gateway=192.168.1.1
add check-gateway=ping distance=2 dst-address=0.0.0.0/0 gateway=192.168.2.1
/ip service
set ftp disabled=yes
set ssh disabled=yes
set telnet disabled=yes
set www disabled=yes
set api disabled=yes
set api-ssl disabled=yes
/ipv6 nd
set [ find default=yes ] advertise-dns=yes
/ppp secret
add name=richard profile=PPT service=pptp
add name=vpn
/routing bfd configuration
add disabled=no interfaces=all min-rx=200ms min-tx=200ms multiplier=5
/system clock
set time-zone-name=America/Guatemala
/system identity
set name=RouterOS
/system scheduler
add interval=4w2d name=autoUpdateDigicomInstagram on-event=\
    updateDigicomInstagram policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:00:09
add interval=4w2d name=autoUpdateDigicomFacebook on-event=\
    updateDigicomFacebook policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:03:09
add interval=4w2d name=autoUpdateDigicomTwitter on-event=updateDigicomTwitter \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:06:09
add interval=4w2d name=autoUpdateDigicomTikTok on-event=updateDigicomTikTok \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:09:09
add interval=4w2d name=autoUpdateDigicomWhatsapp on-event=\
    updateDigicomWhatsapp policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:12:09
add interval=4w2d name=autoUpdateDigicomNetflix on-event=updateDigicomNetflix \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:15:09
add comment="*****///////:::::::::::::::::!!!--QoS.2025.BLACK.USER.V3.0_By.Dig\
    icom--!!!:::::::::::::::://////*****===>" interval=4w2d name=\
    autoUpdateDigicomAmazon on-event=updateDigicomAmazon policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:18:09
add interval=4w2d name=autoUpdateDigicomDisneyPlus on-event=\
    updateDigicomDisneyPlus policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:21:09
add interval=4w2d name=autoUpdateDigicomHBO on-event=updateDigicomHBO policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:24:09
add interval=4w2d name=autoUpdateDigicomYoutube on-event=updateDigicomYoutube \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:27:09
add interval=4w2d name=autoUpdateDigicomGoogle on-event=updateDigicomGoogle \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:30:09
add interval=4w2d name=autoUpdateDigicomClasesV on-event=updateDigicomClasesV \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-01-19 start-time=12:33:09
add interval=10m name=AutoLimiter on-event=\
    "/system script run AutoLimitScript" policy=read,write,test start-date=\
    2025-06-12 start-time=18:15:27
add interval=30s name=CazaEvasores on-event=CazaEvasores policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    start-date=2025-11-10 start-time=11:55:56
/system script
add dont-require-permissions=no name=updateDigicomInstagram owner=admin \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    source=":local domainList \"instagram.com,www.instagram.com,cdninstagram.c\
    om,www.cdninstagram.com,i.instagram.com,api.instagram.com,scontent.cdninst\
    agram.com,graph.instagram.com,help.instagram.com,business.instagram.com,cr\
    eators.instagram.com,shopping.instagram.com,static.cdninstagram.com,ads.in\
    stagram.com,media.cdninstagram.com,ig.me,fbcdn-ig.net,scontent.xx.fbcdn.ne\
    t\"; :local addressList \"Instagram\"; # Eliminar las IP antiguas de la li\
    sta :foreach address in=[/ip firewall address-list find where list=\$addre\
    ssList] do={/ip firewall address-list remove \$address}; # Resolver cada d\
    ominio y agregarlo a la lista :foreach domain in=[split \$domainList \",\"\
    ] do={:local resolvedIP [:resolve \$domain]; :if ([:len \$resolvedIP] > 0)\
    \_do={/ip firewall address-list add address=\$resolvedIP list=\$addressLis\
    t comment=\$domain} else={:log warning \"No se pudo resolver el dominio \$\
    domain\"}}"
add dont-require-permissions=no name=updateDigicomFacebook owner=admin \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    source=":local domainList \"facebook.com,www.facebook.com,m.facebook.com,f\
    bcdn.net,staticxx.facebook.com,connect.facebook.net,messenger.com,www.mess\
    enger.com,fb.me,fb.com,video.xx.fbcdn.net,scontent.xx.fbcdn.net,scontent.f\
    cuz1-1.fna.fbcdn.net,edge-chat.facebook.com,graph.facebook.com,developers.\
    facebook.com,business.facebook.com\"; :local addressList \"Facebook\"; # E\
    liminar las IP antiguas de la lista :foreach address in=[/ip firewall addr\
    ess-list find where list=\$addressList] do={/ip firewall address-list remo\
    ve \$address}; # Resolver cada dominio y agregarlo a la lista :foreach dom\
    ain in=[split \$domainList \",\"] do={:local resolvedIP [:resolve \$domain\
    ]; :if ([:len \$resolvedIP] > 0) do={/ip firewall address-list add address\
    =\$resolvedIP list=\$addressList comment=\$domain} else={:log warning \"No\
    \_se pudo resolver el dominio \$domain\"}}"
add dont-require-permissions=no name=updateDigicomTwitter owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"twitter.com,www.twitter.com,api.twitter.com,mobile.twit\
    ter.com,ads.twitter.com,analytics.twitter.com,pbs.twimg.com,video.twimg.co\
    m,ton.twitter.com,help.twitter.com,abs.twimg.com,o.twimg.com,developer.twi\
    tter.com,cdn.twitter.com,tweetdeck.twitter.com,t.co,x.com,www.x.com,api.x.\
    com,ads.x.com\"; :local addressList \"Twitter\"; # Eliminar las IP antigua\
    s de la lista :foreach address in=[/ip firewall address-list find where li\
    st=\$addressList] do={/ip firewall address-list remove \$address}; # Resol\
    ver cada dominio y agregarlo a la lista :foreach domain in=[split \$domain\
    List \",\"] do={:local resolvedIP [:resolve \$domain]; :if ([:len \$resolv\
    edIP] > 0) do={/ip firewall address-list add address=\$resolvedIP list=\$a\
    ddressList comment=\$domain} else={:log warning \"No se pudo resolver el d\
    ominio \$domain\"}}"
add dont-require-permissions=no name=updateDigicomTikTok owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"tiktok.com,www.tiktok.com,m.tiktok.com,api.tiktok.com,a\
    ds.tiktok.com,log.tiktokv.com,p16-tiktokcdn-com.akamaized.net,mon.tiktokgl\
    obalapps.com,tiktokv.com,sf16-scmcdn-tos.pstatp.com,lf16-tiktokcdn-tos.pst\
    atp.com,ib.tiktokv.com,ns.tiktokv.com,bytedance.net,www.bdxcdn.com,bdxcdn.\
    com,abtest-va.tiktokv.com,sec-tiktokcdn-va.akamaized.net,v16m.tiktokcdn.co\
    m\"; :local addressList \"TikTok\"; # Eliminar las IP antiguas de la lista\
    \_:foreach address in=[/ip firewall address-list find where list=\$address\
    List] do={/ip firewall address-list remove \$address}; # Resolver cada dom\
    inio y agregarlo a la lista :foreach domain in=[split \$domainList \",\"] \
    do={:local resolvedIP [:resolve \$domain]; :if ([:len \$resolvedIP] > 0) d\
    o={/ip firewall address-list add address=\$resolvedIP list=\$addressList c\
    omment=\$domain} else={:log warning \"No se pudo resolver el dominio \$dom\
    ain\"}}"
add dont-require-permissions=no name=updateDigicomWhatsapp owner=admin \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    source=":local domainList \"whatsapp.com,www.whatsapp.com,web.whatsapp.com\
    ,api.whatsapp.com,mmg.whatsapp.net,media.whatsapp.net,cdn.whatsapp.net,cha\
    t.whatsapp.com,call.whatsapp.com,w1.whatsapp.net,w2.whatsapp.net,w3.whatsa\
    pp.net,static.whatsapp.net,st.whatsapp.net,video-cdn.whatsapp.net\"; :loca\
    l addressList \"Whatsapp\"; # Eliminar las IP antiguas de la lista :foreac\
    h address in=[/ip firewall address-list find where list=\$addressList] do=\
    {/ip firewall address-list remove \$address}; # Resolver cada dominio y ag\
    regarlo a la lista :foreach domain in=[split \$domainList \",\"] do={:loca\
    l resolvedIP [:resolve \$domain]; :if ([:len \$resolvedIP] > 0) do={/ip fi\
    rewall address-list add address=\$resolvedIP list=\$addressList comment=\$\
    domain} else={:log warning \"No se pudo resolver el dominio \$domain\"}}"
add dont-require-permissions=no name=updateDigicomNetflix owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"netflix.com,www.netflix.com,api.netflix.com,watch.netfl\
    ix.com,cdn2.netflix.com,video-s3.netflix.com,static.netflix.com,preprod.ne\
    tflix.com,secure.netflix.com,netflix.net,*.netflix.net,*.nflximg.net,nflxi\
    mg.net,*.nflxso.net,nflxso.net,*.nflxvideo.net,nflxvideo.net,*.flicks.com,\
    flicks.com,*.streaming.netflix.com,streaming.netflix.com,*.e.netflix.com,e\
    .netflix.com,*.ns.netflix.com,ns.netflix.com\"; :local addressList \"Netfl\
    ix\"; # Eliminar las IP antiguas de la lista :foreach address in=[/ip fire\
    wall address-list find where list=\$addressList] do={/ip firewall address-\
    list remove \$address}; # Resolver cada dominio y agregarlo a la lista :fo\
    reach domain in=[split \$domainList \",\"] do={:local resolvedIP [:resolve\
    \_\$domain]; :if ([:len \$resolvedIP] > 0) do={/ip firewall address-list a\
    dd address=\$resolvedIP list=\$addressList comment=\$domain} else={:log wa\
    rning \"No se pudo resolver el dominio \$domain\"}}"
add comment="*****///////:::::::::::::::::!!!--QoS.2025.BLACK.USER.V3.0_By.Dig\
    icom--!!!:::::::::::::::://////*****===>" dont-require-permissions=no \
    name=updateDigicomAmazon owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"amazon.com,www.amazon.com,amazonaws.com,s3.amazonaws.co\
    m,primevideo.com,www.primevideo.com,video.amazon.com,www.amazonvideo.com,d\
    p.amazon.com,dpd.amazon.com,images.amazon.com,static.amazon.com,ap-s3-us-w\
    est-2.amazonaws.com,ec2.amazonaws.com,s3-us-west-2.amazonaws.com,s3-ap-sou\
    theast-1.amazonaws.com,aws.amazon.com,www.aws.amazon.com,cloudfront.net,aw\
    sstatic.com,alexa.amazon.com,www.alexa.com,feedproxy.google.com,amazon.co.\
    uk,www.amazon.co.uk\"; :local addressList \"Amazon\"; # Eliminar las IP an\
    tiguas de la lista :foreach address in=[/ip firewall address-list find whe\
    re list=\$addressList] do={/ip firewall address-list remove \$address}; # \
    Resolver cada dominio y agregarlo a la lista :foreach domain in=[split \$d\
    omainList \",\"] do={:local resolvedIP [:resolve \$domain]; :if ([:len \$r\
    esolvedIP] > 0) do={/ip firewall address-list add address=\$resolvedIP lis\
    t=\$addressList comment=\$domain} else={:log warning \"No se pudo resolver\
    \_el dominio \$domain\"}}"
add dont-require-permissions=no name=updateDigicomDisneyPlus owner=admin \
    policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon \
    source=":local domainList \"disneyplus.com,www.disneyplus.com,media.disney\
    plus.com,www.media.disneyplus.com,disneyplusdn.com,cdn.disneyplus.com,stat\
    ic.disneyplus.com,disneyplus.global,disneyplus.co.uk,www.disneyplus.co.uk,\
    *.disneyplus.com,*.disneyplusdn.com,*.disneyplus.global,*.cdn.disneyplus.c\
    om,*.media.disneyplus.com,*.disneyplus.co.uk\"; :local addressList \"Disne\
    yPlus\"; # Eliminar las IP antiguas de la lista :foreach address in=[/ip f\
    irewall address-list find where list=\$addressList] do={/ip firewall addre\
    ss-list remove \$address}; # Resolver cada dominio y agregarlo a la lista \
    :foreach domain in=[split \$domainList \",\"] do={:local resolvedIP [:reso\
    lve \$domain]; :if ([:len \$resolvedIP] > 0) do={/ip firewall address-list\
    \_add address=\$resolvedIP list=\$addressList comment=\$domain} else={:log\
    \_warning \"No se pudo resolver el dominio \$domain\"}}"
add dont-require-permissions=no name=updateDigicomHBO owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"hbo.com,www.hbo.com,hboasia.com,hboeu.com,hbo.com.br,hb\
    omax.com,www.hbomax.com,play.hbomax.com,watch.hbomax.com,cdn.hbomax.com,st\
    atic.hbomax.com,*.hbomax.com,*.hbo.com,*.hboasia.com,*.hboeu.com,*.hbo.com\
    .br,*.hbo.lat,*.hbomaxlatam.com,*.cdn.hbomax.com,*.static.hbomax.com,*.max\
    .com\"; :local addressList \"HBO\"; # Eliminar las IP antiguas de la lista\
    \_:foreach address in=[/ip firewall address-list find where list=\$address\
    List] do={/ip firewall address-list remove \$address}; # Resolver cada dom\
    inio y agregarlo a la lista :foreach domain in=[split \$domainList \",\"] \
    do={:local resolvedIP [:resolve \$domain]; :if ([:len \$resolvedIP] > 0) d\
    o={/ip firewall address-list add address=\$resolvedIP list=\$addressList c\
    omment=\$domain} else={:log warning \"No se pudo resolver el dominio \$dom\
    ain\"}}"
add dont-require-permissions=no name=updateDigicomYoutube owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"youtube.com,googlevideo.com,youtu.be,akamaihd.net\"; :l\
    ocal addressList \"Youtube\"; # Eliminar las IP antiguas de la lista :fore\
    ach address in=[/ip firewall address-list find where list=\$addressList] d\
    o={/ip firewall address-list remove \$address}; # Resolver cada dominio y \
    agregarlo a la lista :foreach domain in=[split \$domainList \",\"] do={:lo\
    cal resolvedIP [:resolve \$domain]; :if ([:len \$resolvedIP] > 0) do={/ip \
    firewall address-list add address=\$resolvedIP list=\$addressList comment=\
    \$domain} else={:log warning \"No se pudo resolver el dominio \$domain\"}}\
    "
add dont-require-permissions=no name=updateDigicomGoogle owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"google.com,www.google.com,accounts.google.com,drive.goo\
    gle.com,docs.google.com,maps.google.com,photos.google.com,play.google.com,\
    translate.google.com,plus.google.com,calendar.google.com,sites.google.com,\
    video.google.com,books.google.com,scholar.google.com,news.google.com,shopp\
    ing.google.com\"; :local addressList \"Google\"; # Eliminar las IP antigua\
    s de la lista :foreach address in=[/ip firewall address-list find where li\
    st=\$addressList] do={/ip firewall address-list remove \$address}; # Resol\
    ver cada dominio y agregarlo a la lista :foreach domain in=[split \$domain\
    List \",\"] do={:local resolvedIP [:resolve \$domain]; :if ([:len \$resolv\
    edIP] > 0) do={/ip firewall address-list add address=\$resolvedIP list=\$a\
    ddressList comment=\$domain} else={:log warning \"No se pudo resolver el d\
    ominio \$domain\"}}"
add dont-require-permissions=no name=updateDigicomClasesV owner=admin policy=\
    ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source=":\
    local domainList \"skype.com,secure.skype.com,login.skype.com,zoom.us,zoom\
    .com,classroom.google.com,meet.google.com,mail.google.com\"; :local addres\
    sList \"Various\"; # Eliminar las IP antiguas de la lista :foreach address\
    \_in=[/ip firewall address-list find where list=\$addressList] do={/ip fir\
    ewall address-list remove \$address}; # Resolver cada dominio y agregarlo \
    a la lista :foreach domain in=[split \$domainList \",\"] do={:local resolv\
    edIP [:resolve \$domain]; :if ([:len \$resolvedIP] > 0) do={/ip firewall a\
    ddress-list add address=\$resolvedIP list=\$addressList comment=\$domain} \
    else={:log warning \"No se pudo resolver el dominio \$domain\"}}"
add comment="DETECTOR AUTOM\C1TICO" dont-require-permissions=no name=\
    CazaEvasores owner=Gerber policy=read,write source=":foreach arp in=[/ip a\
    rp find where interface=bridge1] do={:local ip [/ip arp get \$arp address]\
    ; :local mac [/ip arp get \$arp mac-address]; :if (\$ip != \"192.168.88.1\
    \") do={:if ([/queue simple find where target=(\$ip . \"/32\")] = \"\") do\
    ={:if ([/ip firewall address-list find where address=\$ip list=\"NoLimit\"\
    ] = \"\") do={:if ([/ip firewall address-list find where address=\$ip list\
    =\"Invitados\"] = \"\") do={/ip firewall address-list add address=\$ip lis\
    t=\"Invitados\" timeout=24h comment=(\"EVASOR-\" . \$mac); :log warning (\
    \"IP no autorizada: \" . \$ip . \" MAC: \" . \$mac); :put (\"\?\? EVASOR D\
    ETECTADO: \" . \$ip)}}}}}; /queue simple set [find name=\"Queue-Invitados\
    \"] max-limit=1M/1M"
