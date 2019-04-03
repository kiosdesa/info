import { Component, ViewChild } from '@angular/core';
import { SplashScreen } from '@ionic-native/splash-screen';
import { StatusBar } from '@ionic-native/status-bar';
import { TranslateService } from '@ngx-translate/core';
import { Config, Nav, Platform, ToastController, Events, MenuController, LoadingController } from 'ionic-angular';
import { Storage } from '@ionic/storage';
//import { CacheService } from "ionic-cache";
import { ChooseLangPage, FirstRunPage, SecondRunPage, HomeTab } from '../pages/pages';
import { User, SplitPane, Online, MenuProvider, SmartAudioProvider, CartProvider, NotifyProvider } from '../providers/providers';

interface MenuPageItems 
{
  title: string
  component: any
  icon: any
  color: string
  root: boolean
  notif?: any
  param: any
}
type MenuPageList = MenuPageItems[]

@Component({
  templateUrl: 'app.template.html'
})
export class OfanCoreFrameworkIonic 
{
  rootPage: any;
  pages: MenuPageList;
  _tmpRootPage: any;
  _lang: any = null;

  @ViewChild(Nav) nav: Nav;

  constructor(
    private translate: TranslateService,
    private platform: Platform, 
    private config: Config, 
    private statusBar: StatusBar,
    private splashScreen: SplashScreen, 
    private storage: Storage, 
    //private cache: CacheService, 
    private online: Online, 
    public cart: CartProvider, 
    public notif: NotifyProvider, 
    public user: User, 
    public loading: LoadingController, 
    public events: Events,
    public menuProv: MenuProvider,
    public menu: MenuController,
    public splitPane: SplitPane, 
    public toastCtrl: ToastController, 
    public smartAudio: SmartAudioProvider) 
  { 
    this._lang = this.translate.getBrowserLang();
    this.storage.get('_settings')
    .then((settings) => {
      let decisionPage = settings ? settings.opt_lang ? settings.opt_lang !== 'en' : true ? false : false : false;
      if(decisionPage)
      {
        this._lang = settings.opt_lang;
        this.storage.get('hasSeenTutorial')
        .then((hasSeenTutorial) => {
          this._tmpRootPage = hasSeenTutorial ? SecondRunPage : FirstRunPage;
          setTimeout(() => {
            this.initAll();
          }, 1000);
        });
      }
      else
      {
        this.rootPage = ChooseLangPage;
        this.events.subscribe('lang:set', (data) => 
        {
          this._lang = data.lang;
          this._tmpRootPage = data.page;
          let loading = this.loading.create({
            spinner: 'dots',
            content: 'Loading...'
          });
          loading.present();
          setTimeout(() => {
            loading.dismiss();
            this.initAll();
          }, 1000);
        })
      }
    });
  }

  async initAll()
  {
    // Fungsi call status false adalah tidak di loop
		await this.online.callStatus().subscribe((res: any) => {
      //console.log(res)
      this.loginToken(true);
      this.listenToLoginEvents();
      this.platformReady();
      this.initTranslate();
		},
		(err) => {
      this.loginToken(false);
      this.listenToLoginEvents();
      this.platformReady();
      this.initTranslate();
      this.loopingCheck(13000);
    });
  }

	loopingCheck(time?:number)
	{
		if(!time) time = 24000;
	    setTimeout(() => {
			this.initAll();
	    }, time);
	}

  initTranslate() 
  {
    this.translate.setDefaultLang(this._lang);
    if(this._lang == null)
    {
      const browserLang = this.translate.getBrowserLang();
      if(browserLang) 
      {
        this.translate.use(this.translate.getBrowserLang());
      } 
      else 
      {
        this.translate.use('su'); // Set your opt_lang here
      }
    }
    else
    {
      this.translate.use(this._lang);
    }

    this.translate.get([
      'BACK_BUTTON_TEXT',
      'HOME_TITLE',
      'SHOP_USER_TITLE',
      'KIOS_NEWS',
      'USER_ACCOUNT_TITLE',
			'ADVICE_TITLE',
			'CALENDAR_TITLE',
      'SETTINGS_TITLE'
    ]).subscribe(values => {
      this.config.set('ios', 'backButtonText', values.BACK_BUTTON_TEXT);
      this.pages = this.menuProv.menuToggle(values);
    });
  }

  listenToLoginEvents()
  {
    /*this.events.subscribe('glob:openpage', (pageListen) => {
      //this.openPage(pageListen);
    });*/
  }

  async loginToken(online?: boolean)
  {
    if(online == true)
    {
      await this.storage.get('loginToken').then((isToken) => {
        if(isToken)
        {
          //console.log(isToken);
          this.user.checktoken({init:'buyer-token', pack:{token:isToken}})
          .subscribe((resp: any) => {
            const itemToken = resp.tokenBuyer.items;
            let toast = this.toastCtrl.create({
              message: itemToken.message,
              cssClass: 'success-toast',
              duration: 5000,
              position: 'top'
            });
            //console.log(itemToken);
            if(itemToken.approve == false)
            {
              toast.present();
              this.enableMenu(false);
              this.rootPage = this._tmpRootPage;
            }
            else
            {
              this.cart.count();
              //console.log(this.cart._cartCount)
              //this.notif.store(this.cart._cartCount, 'cart');
              this.enableMenu(true);
              this.rootPage = HomeTab;
            }
          }, 
          (err) => {
            this.enableMenu(false);
            this.rootPage = this._tmpRootPage;
          });
        }
        else
        {
          //console.log('no tok:',isToken);
          this.notif.removesAll()
          this.enableMenu(false);
          this.rootPage = this._tmpRootPage;
        }
      });
    }
    else
    {
      this.enableMenu(false);
      this.rootPage = this._tmpRootPage;
    }
  }

  platformReady()
  {
    this.platform.ready().then(() => {
      //this.cache.clearExpired();
      //this.cache.setDefaultTTL(60 * 60 * 6);
      //this.cache.setOfflineInvalidate(false);
      this.statusBar.styleDefault();
      this.splashScreen.hide();
      this.smartAudio.preload('announce', 'assets/audio/quite-impressed.ogg');
      this.smartAudio.preload('order', 'assets/audio/notifs-bumdesku.ogg');
    });
  }

  enableMenu(loggedIn: boolean) 
  {
    this.menu.enable(loggedIn, 'sideMainMenu');
  }

  openPage(page) 
  {
    if(page.index == undefined)
    {
      this.nav.push(page.component, {paramRoots:page.param}, {animate:true})
    }
    else
    {
      if(this.nav.canGoBack())
      {
        // Handle navigation if page before is too many push
        this.nav.popToRoot();
      }
      this.events.publish('tab:clicked',{tab:page.index});
    }
  }
}
