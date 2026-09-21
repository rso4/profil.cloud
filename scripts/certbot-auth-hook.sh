#!/usr/bin/env bash
# Certbot DNS-01 auth hook untuk profil.cloud
# Menampilkan & menulis record TXT ke file, lalu menunggu propagasi DNS.
set -euo pipefail

DOMAIN="${CERTBOT_DOMAIN:-}"
VALIDATION="${CERTBOT_VALIDATION:-}"
CHALLENGE="_acme-challenge.${DOMAIN}"
OUTFILE="/var/www/profil.cloud/letsencrypt/txt-records.txt"

# Tulis record TXT ke file agar bisa dibaca
{
    echo "================================================================"
    echo "  TAMBAHKAN RECORD TXT BERIKUT DI PANEL DNS HOSTINGER:"
    echo "================================================================"
    echo "  Tipe  : TXT"
    echo "  Nama  : ${CHALLENGE}"
    echo "  Nilai : ${VALIDATION}"
    echo "================================================================"
    echo "  Menunggu propagasi DNS (maks 300 detik)..."
    echo ""
} | tee -a "${OUTFILE}"

# Poll DNS sampai record TXT terlihat.
# Coba beberapa resolver publik agar tidak terjebak cache nilai lama.
# Timeout 15 menit agar memberi waktu menambahkan record di panel DNS.
for i in $(seq 1 90); do
    for RESOLVER in 1.1.1.1 8.8.8.8; do
        if dig @"${RESOLVER}" +short TXT "${CHALLENGE}" 2>/dev/null | grep -q "${VALIDATION}"; then
            echo "  [OK] Record TXT terdeteksi (${RESOLVER}) setelah $((i*10)) detik." | tee -a "${OUTFILE}"
            exit 0
        fi
    done
    sleep 10
done

echo "  [GAGAL] Record TXT tidak terdeteksi dalam 900 detik." | tee -a "${OUTFILE}"
exit 1
