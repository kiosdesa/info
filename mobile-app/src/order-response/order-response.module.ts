import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { OfanCoreShareModule } from './../../module/ofancore-share.module';
import { OfanCoreShareIconsModule } from '../../module/ofancore-share.icon.modul';
import { StarRatingModule } from 'ionic3-star-rating';
import { IonicPageModule } from 'ionic-angular';
import { OrderResponsePage } from './order-response';

@NgModule({
  declarations: [
    OrderResponsePage,
  ],
  imports: [
    IonicPageModule.forChild(OrderResponsePage),
    TranslateModule.forChild(),
    StarRatingModule,
    OfanCoreShareModule,
    OfanCoreShareIconsModule
  ],
  entryComponents: [
    OrderResponsePage,
  ],
})
export class OrderResponsePageModule {}
