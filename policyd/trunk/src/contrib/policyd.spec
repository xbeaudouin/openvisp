%define ver v1.80
%define rel 1
#%{!?key: %define fedora 0}

%define install_path /usr/local/%{name}

%define release %(release="`rpm -q --queryformat='.%{VERSION}' redhat-release 2>/dev/null`" ; if test $? != 0 ; then release="`rpm -q --queryformat='.%{VERSION}' fedora-release 2>/dev/null`" ; if test $? != 0 ; then release="" ; fi ; fi ; echo "$release")
%define vendor %(vendor="`rpm -q redhat-release 2>/dev/null`" ; if test $? != 0 ; then vendor="`rpm -q fedora-release 2>/dev/null`" ; if test $? != 0 ; then vendor="" ; fi ; fi ; echo "$vendor")

%if %{_vendor}=="redhat"
%define redhat 1
%else %if %{_vendor}=="fedora"
%define fedora 1
%endif

Summary: Postfix Policyd Daemon
Name: policyd
Version: %{ver}
Release: %{rel}%{release}
URL: http://policyd.sourceforge.net/
Source0: http://policyd.sourceforge.net/%{name}-%{ver}.tar.gz
License: GNU GPL v2
Group: System/Daemons
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildRequires: gcc
Requires: postfix => 2.1 mysql => 3 mysql-server => 3

%description
Policyd is a policy server for Postfix (written in C) that enables
advanced Greylisting with many other anti-spam facilities. See the
docs and policyd.conf for features that are ever being augmented. 
It needs MySQL v3 or greater and is currently only certified for
MySQL v4.

%prep

%setup -q

%build
make build

%install
rm -rf $RPM_BUILD_ROOT

# Misc stuff
%if %{fedora}
install -d $RPM_BUILD_ROOT/usr/sbin
install -d $RPM_BUILD_ROOT/etc/postfix
install -d $RPM_BUILD_ROOT/etc/rc.d/init.d
install -d $RPM_BUILD_ROOT/etc/cron.d
install -m755 contrib/%{name}.init.fedora $RPM_BUILD_ROOT/etc/rc.d/init.d/%{name}
install -m644 contrib/%{name}.cron $RPM_BUILD_ROOT/etc/cron.d/%{name}
install -m755 %{name} $RPM_BUILD_ROOT/usr/sbin/%{name}
install -m755 cleanup $RPM_BUILD_ROOT/usr/sbin/cleanup
install -m755 stats $RPM_BUILD_ROOT/usr/sbin/stats
install -m600 %{name}.conf $RPM_BUILD_ROOT/etc/postfix/%{name}.conf
%else %if %{redhat}
sed -i 's|PROG=\"/usr/local/policyd/policyd\"|PROG=\"%{_sbindir}/policyd\"|' contrib/%{name}.%{_vendor}.init
sed -i 's|CONF=\"/usr/local/policyd/policyd.conf\"|CONF=\"%{_sysconfdir}/policyd.conf\"|' contrib/%{name}.%{_vendor}.init
install -d $RPM_BUILD_ROOT%{_sysconfdir}/postfix
install -d $RPM_BUILD_ROOT%{_sysconfdir}/rc.d/init.d
install -d $RPM_BUILD_ROOT%{_sysconfdir}/cron.d
install -d $RPM_BUILD_ROOT%{_sbindir}
install -d $RPM_BUILD_ROOT%{_sysconfdir}/cron.d
install -m755 contrib/%{name}.%{_vendor}.init $RPM_BUILD_ROOT/%{_sysconfdir}/rc.d/init.d/%{name}
install -m644 contrib/%{name}.cron $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/%{name}
install -m755 %{name} $RPM_BUILD_ROOT%{_sbindir}
install -m755 cleanup $RPM_BUILD_ROOT%{_sbindir}/cleanup
install -m755 stats $RPM_BUILD_ROOT%{_sbindir}
install -m600 %{name}.conf $RPM_BUILD_ROOT%{_sysconfdir}/%{name}.conf

%else
install -d $RPM_BUILD_ROOT%{install_path}
install -d $RPM_BUILD_ROOT/etc/cron.d
#install -m755 contrib/%{name}.init $RPM_BUILD_ROOT%{install_path}
install -m644 contrib/%{name}.cron $RPM_BUILD_ROOT/etc/cron.d/%{name}
install -m755 %{name} $RPM_BUILD_ROOT%{install_path}/%{name}
install -m755 cleanup $RPM_BUILD_ROOT%{install_path}/cleanup
install -m755 stats $RPM_BUILD_ROOT%{install_path}/stats
install -m600 %{name}.conf $RPM_BUILD_ROOT%{install_path}/%{name}.conf
%endif

%if %{fedora} || %{redhat}
%post
/sbin/chkconfig --add %{name}
%endif

%if %{fedora} || %{redhat}
%postun
/sbin/service %{name} condrestart > /dev/null 2>&1 || :
%endif

%if %{fedora} || %{redhat}
%preun
if [ "$1" = 0 ]
then
	/sbin/service %{name} stop > /dev/null 2>&1 || :
	/sbin/chkconfig --del %{name}
fi
%endif

%files
%defattr(-,root,root)
#%doc Changelog.txt DATABASE.mysql License.txt README.txt SUPPORT.txt TODO.txt WHITELIST.sql
%if %{fedora}
%attr(0600,root,root) %config(noreplace) %{_sysconfdir}/postfix/%{name}.conf
%attr(0755,root,root) %config /etc/rc.d/init.d/%{name}
%attr(0644,root,root) %config /etc/cron.d/%{name}
%attr(0755,root,root) %{_sbindir}/%{name}
%attr(0755,root,root) %{_sbindir}/cleanup
%attr(0755,root,root) %{_sbindir}/stats

%else %if %{redhat}
%doc %attr(0644,root,root) Changelog.txt DATABASE.mysql License.txt README.txt doc/*
%attr(0600,root,root) %config(noreplace) %{_sysconfdir}/%{name}.conf
%attr(0755,root,root) %config %{_sysconfdir}/rc.d/init.d/%{name}
%attr(0644,root,root) %config %{_sysconfdir}/cron.d/%{name}
%attr(0755,root,root) %{_sbindir}/%{name}
%attr(0755,root,root) %{_sbindir}/cleanup
%attr(0755,root,root) %{_sbindir}/stats

%else
%attr(0600,root,root) %config(noreplace) %{install_path}/%{name}.conf
#%attr(0755,root,root) %config %{install_path}/%{name}.init
%attr(0644,root,root) %config /etc/cron.d/%{name}
%attr(0755,root,root) %{install_path}/%{name}
%attr(0755,root,root) %{install_path}/cleanup
%attr(0755,root,root) %{install_path}/stats

%endif

%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

%changelog
* Tue Mar 14 2006 Tony Earnshaw <tonni@barlaeus.nl>
- Red Hat RHAS/RHEL adaptation (left the original Fedora magic
- severely alone, simply concentrated on Red Hat).  Doc files
- added.

* Fri Aug 26 2005 Catalin Muresan <cata@astral.ro>
- added fedora, install_path

* Fri Aug 26 2005 Catalin Muresan <cata@astral.ro>
- First spec version

