# Copyright 1999-2005 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
# $Header: /var/tmp/t/openvisp/policyd/src/contrib/policyd-1.73.ebuild,v 1.1 2009-09-26 09:47:51 kiwi Exp $

inherit eutils

DESCRIPTION="Policyd daemon for postfix"
HOMEPAGE="http://policyd.sf.net/"
SRC_URI="http://policyd.sourceforge.net/${PN}-v${PV}.tar.gz mirror://sourceforge/${PN}/${PN}-v${PV}.tar.gz"
LICENSE="GPL-2"
SLOT="0"
KEYWORDS="x86 amd64"
IUSE=""
DEPEND=">=mail-mta/postfix-2.1.5
	>=dev-db/mysql-4.0.24
	>=dev-libs/openssl-0.9.7e-r1"

S=${WORKDIR}/${PN}-v${PV}

src_unpack() {
	unpack ${A}
	cd ${S}
	epatch ${FILESDIR}/${PV}-Makefile.patch || die "epatch Makefile.patch failed"
}

src_compile() {
	cd ${S}
	cat policyd.conf | sed -e s/UID=0/UID=65534/ | sed -e s/GID=0/GID=65534/ | sed -e s/DAEMON=0/DAEMON=1/ | sed -e s/DEBUG=3/DEBUG=0/ | sed -e s/DATABASE_KEEPALIVE=0/DATABASE_KEEPALIVE=1/ > policyd.conf.new
	rm policyd.conf
	mv policyd.conf.new policyd.conf
	emake build || die "emake failed"
}

src_install() {
	insopts -o root -g nobody -m 0750
	dosbin policyd cleanup stats
	insinto /etc
	insopts -o root -g nobody -m 0640
	doins policyd.conf
	exeinto /etc/cron.hourly
	insopts -o root -g nobody -m 0700
	newexe ${FILESDIR}/${PN}.gentoo.cron ${PN}.cron
	exeinto /etc/cron.daily
	insopts -o root -g nobody -m 0700
	newexe ${FILESDIR}/${PN}-stats.cron ${PN}.stats.cron
	dodoc Changelog.txt DATABASE.mysql License.txt README.txt doc/support.txt doc/blacklist_helo.sql doc/whitelist.sql
	newinitd ${FILESDIR}/${PN}.gentoo.init ${PN}
}

pkg_postinst() {
	einfo "To make use of policyd, please update your postfix config:"
	einfo "Add \"check_policy_service inet:127.0.0.1:10031\" to"
	einfo "smtpd_recipient restrictions setting in your /etc/postfix/main.cf"
	einfo "and restart postfix."
	einfo "Also remember to make the daemon start durig system boot:"
	einfo "  rc-update add policyd default"
	ewarn "Read the documentation for more info."
	einfo "Follow the maillist please."
}
