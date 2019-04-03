import { HttpClient, HttpClientModule } from '@angular/common/http';
import { ErrorHandler, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
//import { Camera } from '@ionic-native/camera';
import { SplashScreen } from '@ionic-native/splash-screen';
import { StatusBar } from '@ionic-native/status-bar';
import { IonicStorageModule, Storage } from '@ionic/storage';
//import { CacheModule } from 'ionic-cache';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { IonicApp, IonicErrorHandler, IonicModule } from 'ionic-angular';
import { NativeAudio } from '@ionic-native/native-audio';
//import { SeparateNumberPipe } from '../pipe/separate-number';
//import { SeparateNumberFormatterDirective } from '../directives/separate-number-dr';

import { 
  Settings, User, Api, Online, SplitPane, RequestApiProvider, SensusProvider, 
  MessagingProvider, Bumdesnews, InfoProvider, MenuProvider, ProductProvider, SellerProvider, CartProvider, 
  NotifyProvider, UploadProvider, AppProvider, SmartAudioProvider, NativePageTransitions 
} from '../providers/providers';
import { OfanCoreFrameworkIonic } from './app.component';

export function createTranslateLoader(http: HttpClient) 
{
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}

export function provideSettings(storage: Storage, translate: TranslateService) 
{
  const browserLang = translate.getBrowserLang();
  let decisionLang = browserLang ? browserLang : 'id';
  return new Settings(storage, {
    opt_cache: true,
    opt_lang: decisionLang
  });
}

@NgModule({
  declarations: [
    OfanCoreFrameworkIonic, //SeparateNumberPipe, SeparateNumberFormatterDirective
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: (createTranslateLoader),
        deps: [HttpClient]
      }
    }),
    IonicModule.forRoot(OfanCoreFrameworkIonic,{tabsHideOnSubPages: true}),
    IonicStorageModule.forRoot(),
    //CacheModule.forRoot()
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    OfanCoreFrameworkIonic
  ],
  providers: [
    NativePageTransitions,
    Api,
    Online, 
    SplitPane,
    RequestApiProvider,
    User,
    //Camera,
    SplashScreen,
    StatusBar,
    { provide: Settings, useFactory: provideSettings, deps: [Storage, TranslateService] },
    { provide: ErrorHandler, useClass: IonicErrorHandler },
    AppProvider,
    SensusProvider,
    MessagingProvider,
    Bumdesnews,
    InfoProvider,
    MenuProvider,
    NotifyProvider,
    UploadProvider,
    SmartAudioProvider,
    NativeAudio,
    ProductProvider,
    SellerProvider,
    CartProvider
  ]
})
export class AppModule {}
